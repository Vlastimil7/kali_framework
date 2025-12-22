<?php

namespace Models;

use Core\Database;
use PDO;

class Order
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM orders WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByOrderNumber(string $orderNumber): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_number = :n LIMIT 1");
        $stmt->execute([':n' => $orderNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByComgatePayId(string $payId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE comgate_pay_id = :pid LIMIT 1");
        $stmt->execute([':pid' => $payId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Vytvoří objednávku a vrátí order_id (int).
     * $data musí obsahovat billing_* + total_amount_cents.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO orders (
                    order_number,
                    billing_name, billing_email, billing_phone,
                    billing_street, billing_house_no, billing_city, billing_zip,
                    billing_company, billing_ico, billing_dic,
                    total_amount_cents, currency, status,
                    payment_method,
                    comgate_pay_id, comgate_ref_id
                ) VALUES (
                    :order_number,
                    :billing_name, :billing_email, :billing_phone,
                    :billing_street, :billing_house_no, :billing_city, :billing_zip,
                    :billing_company, :billing_ico, :billing_dic,
                    :total_amount_cents, :currency, :status,
                    :payment_method,
                    :comgate_pay_id, :comgate_ref_id
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':order_number' => $data['order_number'],

            ':billing_name'  => $data['billing_name'],
            ':billing_email' => $data['billing_email'],
            ':billing_phone' => $data['billing_phone'],
            ':billing_street' => $data['billing_street'],
            ':billing_house_no' => $data['billing_house_no'],
            ':billing_city' => $data['billing_city'],
            ':billing_zip' => $data['billing_zip'],

            ':billing_company' => $data['billing_company'] ?? null,
            ':billing_ico'     => $data['billing_ico'] ?? null,
            ':billing_dic'     => $data['billing_dic'] ?? null,

            ':total_amount_cents' => (int)$data['total_amount_cents'],
            ':currency' => $data['currency'] ?? 'CZK',
            ':status'   => $data['status'] ?? 'pending',

            ':payment_method' => $data['payment_method'] ?? null,
            ':comgate_pay_id' => $data['comgate_pay_id'] ?? null,
            ':comgate_ref_id' => $data['comgate_ref_id'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateAdmin(int $id, array $data): array
    {
        try {
            $sql = "UPDATE orders
                    SET billing_name = :billing_name,
                        billing_email = :billing_email,
                        billing_phone = :billing_phone,
                        billing_street = :billing_street,
                        billing_house_no = :billing_house_no,
                        billing_city = :billing_city,
                        billing_zip = :billing_zip,
                        billing_company = :billing_company,
                        billing_ico = :billing_ico,
                        billing_dic = :billing_dic
                    WHERE id = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':billing_name'  => $data['billing_name'],
                ':billing_email' => $data['billing_email'],
                ':billing_phone' => $data['billing_phone'],
                ':billing_street' => $data['billing_street'],
                ':billing_house_no' => $data['billing_house_no'],
                ':billing_city' => $data['billing_city'],
                ':billing_zip' => $data['billing_zip'],
                ':billing_company' => $data['billing_company'] ?? null,
                ':billing_ico' => $data['billing_ico'] ?? null,
                ':billing_dic' => $data['billing_dic'] ?? null,
                ':id' => $id,
            ]);

            return ['success' => true];
        } catch (\Throwable $e) {
            \Helpers\Logger::exception($e, [
                'model' => 'Order',
                'action' => 'updateAdmin',
                'id' => $id,
                'data' => $data,
            ]);
            return ['success' => false, 'message' => 'Chyba při ukládání objednávky.'];
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = :s WHERE id = :id");
        $stmt->execute([':id' => $id, ':s' => $status]);
        return $stmt->rowCount() > 0;
    }

    public function markPaid(int $id): bool
    {
        // ochrana: nenastavuj paid znovu
        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = 'paid', paid_at = NOW()
            WHERE id = :id AND status <> 'paid'
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getListAdmin(int $limit = 200): array
    {
        $sql = "SELECT 
                    id,
                    order_number,
                    billing_name,
                    billing_email,
                    billing_phone,
                    total_amount_cents,
                    currency,
                    status,
                    created_at,
                    paid_at
                FROM orders
                ORDER BY id DESC
                LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function setStatusAdmin(int $id, string $status, int $adminId, string $note = ''): array
    {
        try {
            $allowed = ['pending', 'awaiting_payment', 'paid', 'canceled', 'refunded', 'expired'];
            if (!in_array($status, $allowed, true)) {
                return ['success' => false, 'message' => 'Neplatný status.'];
            }

            $adminId = $adminId > 0 ? $adminId : null;
            $note = trim($note);

            $set = ["status = :status"];
            $params = [
                ':id' => $id,
                ':status' => $status,
            ];

            // Volitelně: ukládej i obecnou admin poznámku
            if ($note !== '') {
                $set[] = "admin_note = :admin_note";
                $params[':admin_note'] = $note;
            }

            // Audit podle statusu
            if ($status === 'canceled') {
                $set[] = "canceled_at = NOW()";
                $set[] = "canceled_by = :admin_id";
                $set[] = "cancel_reason = :reason";
                $params[':admin_id'] = $adminId;
                $params[':reason'] = ($note !== '' ? $note : null);
            }

            if ($status === 'refunded') {
                $set[] = "refunded_at = NOW()";
                $set[] = "refunded_by = :admin_id";
                $set[] = "refund_reason = :reason";
                $params[':admin_id'] = $adminId;
                $params[':reason'] = ($note !== '' ? $note : null);
            }

            if ($status === 'expired') {
                $set[] = "expired_at = NOW()";
                $set[] = "expired_by = :admin_id";
                $set[] = "expire_reason = :reason";
                $params[':admin_id'] = $adminId;
                $params[':reason'] = ($note !== '' ? $note : null);
            }

            // (volitelné) když vracíš objednávku do "pending/awaiting_payment",
            // můžeš čistit auditní pole - nechávám vypnuté, protože audit je užitečný
            // if (in_array($status, ['pending','awaiting_payment'], true)) {
            //     $set[] = "canceled_at = NULL, canceled_by = NULL, cancel_reason = NULL";
            //     $set[] = "refunded_at = NULL, refunded_by = NULL, refund_reason = NULL";
            //     $set[] = "expired_at = NULL, expired_by = NULL, expire_reason = NULL";
            // }

            $sql = "UPDATE orders SET " . implode(", ", $set) . " WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() !== 1) {
                return ['success' => false, 'message' => 'Objednávku se nepodařilo aktualizovat.'];
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            \Helpers\Logger::exception($e, [
                'model' => 'Order',
                'action' => 'setStatusAdmin',
                'id' => $id,
                'status' => $status,
                'adminId' => $adminId,
            ]);
            return ['success' => false, 'message' => 'Chyba při změně stavu objednávky.'];
        }
    }

    public function markEmailSent(int $id, string $type): void
    {
        $map = [
            'paid' => 'email_paid_sent_at',
            'canceled' => 'email_canceled_sent_at',
            'refunded' => 'email_refunded_sent_at',
            'expired' => 'email_expired_sent_at',
        ];
        $col = $map[$type] ?? null;
        if (!$col) return;

        $st = $this->db->prepare("UPDATE orders SET {$col} = NOW() WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
    }

    public function markPdfGenerated(int $id, string $path): void
    {
        $st = $this->db->prepare("UPDATE orders SET pdf_generated_at = NOW(), pdf_path = :p WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id, ':p' => $path]);
    }

    public function wasEmailSent(array $order, string $type): bool
    {
        $map = [
            'paid' => 'email_paid_sent_at',
            'canceled' => 'email_canceled_sent_at',
            'refunded' => 'email_refunded_sent_at',
            'expired' => 'email_expired_sent_at',
        ];
        $col = $map[$type] ?? null;
        return $col ? !empty($order[$col]) : false;
    }
}
