<?php

namespace Controllers\Front;

use Core\Controller;
use Services\Payments\ComgateService;
use Services\Vouchers\OrderService;
use Helpers\Logger;

class PaymentController extends Controller
{
    private ComgateService $comgate;
    private OrderService $orders;

    public function __construct()
    {
        parent::__construct();
        $this->comgate = new ComgateService();
        $this->orders = new OrderService();
    }

    public function comgateReturn()
    {
        $transId = $_GET['id'] ?? null;
        $refId   = $_GET['refId'] ?? null;
        $paramStatus = $_GET['status'] ?? null;

        Logger::info("Comgate RETURN hit", ['get' => $_GET]);

        if (!$refId) {
            $this->show404();
            return;
        }

        $status = $paramStatus ? strtolower((string)$paramStatus) : null;

        if ($transId) {
            $apiStatus = $this->comgate->getPaymentStatus((string)$transId);
            if ($apiStatus !== null) $status = strtolower((string)$apiStatus);
        }

        $this->view('payments/comgate_return', [
            'title'   => 'Stav platby | Midobarbershop',
            'status'  => $status,
            'refId'   => (string)$refId,
            'transId' => $transId ? (string)$transId : null,
        ]);
    }

    public function comgateNotify()
    {
        file_put_contents(BASE_PATH.'/storage/logs/notify.log', date('c')." HIT\n".print_r($_POST,true)."\n\n", FILE_APPEND);

        $post = $_POST ?? [];
        Logger::info("Comgate NOTIFY hit", ['post' => $post]);

        $normalized = $this->comgate->normalizeNotification($post);
        if ($normalized === null) {
            Logger::warning('Comgate NOTIFY ignored (invalid payload)', [
                'post' => $_POST,
            ]);

            http_response_code(200);
            echo 'OK';
            exit;
        }

        $ok = $this->orders->processGatewayNotification($normalized);
        if (!$ok) {
            http_response_code(500);
            echo "ERROR";
            return;
        }

        http_response_code(200);
        echo "OK";
    }
}
