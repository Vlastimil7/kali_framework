<?php

namespace Services\Payments;

use Comgate\SDK\Comgate;
use Comgate\SDK\Entity\Codes\CurrencyCode;
use Comgate\SDK\Entity\Codes\PaymentMethodCode;
use Comgate\SDK\Entity\Codes\RequestCode;
use Comgate\SDK\Entity\Money;
use Comgate\SDK\Entity\Payment;
use Comgate\SDK\Entity\Refund;
use Comgate\SDK\Exception\ApiException;
use Helpers\Logger;

class ComgateService
{
    private $client;
    private array $config;

    public function __construct()
    {
        $this->config = (array)config('payments.comgate', []);

        try {
            $this->client = Comgate::defaults()
                ->setMerchant((string)($this->config['merchant'] ?? ''))
                ->setSecret((string)($this->config['secret'] ?? ''))
                ->createClient();

            Logger::info('Comgate client initialised', [
                'merchant' => $this->config['merchant'] ?? '',
                'test'     => (bool)($this->config['test'] ?? true),
            ]);
        } catch (\Throwable $e) {
            Logger::exception($e, ['context' => 'ComgateService::__construct']);
            throw $e;
        }
    }

    /**
     * Založení platby v Comgate.
     *
     * $orderData:
     *  - reference  (string) – naše order_id
     *  - priceCents (int)
     *  - email      (string)
     *  - phone      (?string)
     *  - name       (string)
     */
    public function createPayment(array $orderData): array
    {
        $payment = new Payment();
        $money   = Money::ofCents((int)$orderData['priceCents']);

        $payment
            ->setPrice($money)
            ->setCurrency(CurrencyCode::CZK)
            ->setLabel('Voucher objednávka ' . $orderData['reference'])
            ->setReferenceId((string)$orderData['reference'])
            ->setEmail((string)$orderData['email'])
            ->setFullName((string)$orderData['name'])
            ->addMethod(PaymentMethodCode::ALL)
            ->setTest((bool)($this->config['test'] ?? true));

        $returnUrl = (string)($this->config['return_url'] ?? '');
        $notifyUrl = (string)($this->config['notify_url'] ?? '');

        // Return/Notify – SDK se může lišit, proto robustně:
        $this->trySet($payment, 'setReturnUrl', $returnUrl);
        $this->trySet($payment, 'setNotifyUrl', $notifyUrl);

        // Některá SDK místo toho používají "setUrl" nebo parametry:
        $payment->setParam('return_url', $returnUrl);
        $payment->setParam('notify_url', $notifyUrl);

        // lang/country (pokud se ti hodí)
        $payment->setParam('lang', (string)($this->config['language'] ?? 'cs'));
        $payment->setParam('country', (string)($this->config['country'] ?? 'CZ'));

        // phone jako custom param
        if (!empty($orderData['phone'])) {
            $payment->setParam('phone', (string)$orderData['phone']);
        }

        try {
            Logger::info('Comgate createPayment request', [
                'reference'  => (string)$orderData['reference'],
                'priceCents' => (int)$orderData['priceCents'],
                'email'      => (string)$orderData['email'],
                'return'     => $returnUrl,
                'notify'     => $notifyUrl,
                'test'       => (bool)($this->config['test'] ?? true),
            ]);

            $response = $this->client->createPayment($payment);

            Logger::info('Comgate createPayment response', [
                'reference' => (string)$orderData['reference'],
                'code'      => method_exists($response, 'getCode') ? $response->getCode() : null,
                'message'   => method_exists($response, 'getMessage') ? $response->getMessage() : null,
                'redirect'  => method_exists($response, 'getRedirect') ? $response->getRedirect() : null,
                'transId'   => method_exists($response, 'getTransId') ? $response->getTransId() : null,
            ]);

            if (method_exists($response, 'getCode') && $response->getCode() !== RequestCode::OK) {
                return ['success' => false, 'error' => $response->getMessage(), 'code' => $response->getCode()];
            }

            $transId = method_exists($response, 'getTransId') ? (string)$response->getTransId() : '';
            $redir   = method_exists($response, 'getRedirect') ? (string)$response->getRedirect() : '';

            if ($transId === '' || $redir === '') {
                return ['success' => false, 'error' => 'Comgate nevrátil transId/redirect.'];
            }

            return ['success' => true, 'transId' => $transId, 'redirectUrl' => $redir];
        } catch (ApiException $e) {
            Logger::exception($e, ['context' => 'ComgateService::createPayment']);
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            Logger::exception($e, ['context' => 'ComgateService::createPayment']);
            return ['success' => false, 'error' => 'Neočekávaná chyba při zakládání platby.'];
        }
    }

    /**
     * Notify normalizace – z POSTu vytáhne jednotný tvar.
     * (Ověření podpisu dle SDK/API případně doplníš sem.)
     */
    public function normalizeNotification(array $post): ?array
    {
        foreach (['refId', 'transId', 'status'] as $k) {
            if (!isset($post[$k]) || $post[$k] === '') {
                Logger::warning('Comgate NOTIFY missing required field', [
                    'missing' => $k,
                    'post' => $post,
                ]);
                return null;
            }
        }

        return [
            'refId'    => (int)$post['refId'],
            'transId'  => (string)$post['transId'],
            'status'   => (string)$post['status'], // PAID / CANCELLED / ...
            'price'    => isset($post['price']) ? (int)$post['price'] : null,
            'currency' => $post['curr'] ?? null,
            'email'    => $post['email'] ?? null,
            'raw'      => $post,
        ];
    }

    /**
     * Status platby – snaží se použít metodu ze SDK (názvy se liší).
     * Vrací string status nebo null.
     */
    public function getPaymentStatus(string $transId): ?string
    {
        try {
            // Zkus různé metody dle SDK
            if (method_exists($this->client, 'getStatus')) {
                $resp = $this->client->getStatus($transId);
                return $this->extractStatus($resp);
            }
            if (method_exists($this->client, 'status')) {
                $resp = $this->client->status($transId);
                return $this->extractStatus($resp);
            }
            if (method_exists($this->client, 'getPaymentDetail')) {
                $resp = $this->client->getPaymentDetail($transId);
                return $this->extractStatus($resp);
            }

            Logger::warning('Comgate SDK has no known status method', ['transId' => $transId]);
            return null;
        } catch (\Throwable $e) {
            Logger::exception($e, ['context' => 'ComgateService::getPaymentStatus', 'transId' => $transId]);
            return null;
        }
    }

    /**
     * Refund – vrací success/error.
     * Pozor: názvy metod v SDK se liší → opět robustní detekce.
     */
    public function refund(string $transId, ?int $amountCents = null): array
    {
        try {
            Logger::info('Comgate refund request', ['transId' => $transId, 'amountCents' => $amountCents]);

            // SDK vyžaduje amount vždy -> když null, tak vyplň v OrderService (nebo tady, pokud umíš zjistit total)
            if ($amountCents === null) {
                return ['success' => false, 'error' => 'amountCents je povinné pro refund v tomto SDK (pošli total_amount_cents).'];
            }

            if (method_exists($this->client, 'refundPayment')) {
                $refund = (new Refund())
                    ->setTransId($transId)
                    ->setAmount(Money::ofCents($amountCents))
                    ->setTest((bool)($this->config['test'] ?? true));

                $resp = $this->client->refundPayment($refund);
                return $this->okFromResponse($resp);
            }

            // fallback, kdyby někdy SDK mělo i refund($transId, Money)
            if (method_exists($this->client, 'refund')) {
                $resp = $this->client->refund($transId, Money::ofCents($amountCents));
                return $this->okFromResponse($resp);
            }

            return ['success' => false, 'error' => 'SDK nemá metodu pro refund.'];
        } catch (ApiException $e) {
            Logger::exception($e, ['context' => 'ComgateService::refund', 'transId' => $transId]);
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            Logger::exception($e, ['context' => 'ComgateService::refund', 'transId' => $transId]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ----------------- helpers -----------------

    private function trySet(object $obj, string $method, mixed $value): void
    {
        if (method_exists($obj, $method)) {
            try {
                $obj->{$method}($value);
            } catch (\Throwable $e) {
                Logger::warning('Comgate trySet failed', ['method' => $method, 'error' => $e->getMessage()]);
            }
        }
    }

    private function extractStatus(object $resp): ?string
    {
        foreach (['getStatus', 'getState', 'getPaymentStatus'] as $m) {
            if (method_exists($resp, $m)) {
                $s = (string)$resp->{$m}();
                return $s !== '' ? strtolower($s) : null;
            }
        }
        return null;
    }

    private function okFromResponse(object $resp): array
    {
        $code = method_exists($resp, 'getCode') ? $resp->getCode() : null;
        $msg  = method_exists($resp, 'getMessage') ? $resp->getMessage() : null;

        Logger::info('Comgate response', ['code' => $code, 'message' => $msg]);

        if ($code !== null && defined(RequestCode::class . '::OK') && $code !== RequestCode::OK) {
            return ['success' => false, 'error' => $msg ?: 'Refund operace se nezdařila.'];
        }
        return ['success' => true];
    }


}
