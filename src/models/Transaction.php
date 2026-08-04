<?php

// src/Models/Transaction.php

namespace Models;

use Core\Database;
use Helpers\Logger;
use PDO;

class Transaction
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): array
    {
        try {
            $sql = 'INSERT INTO transactions (
                        order_id,
                        gateway,
                        gateway_payment_id,
                        amount_cents,
                        currency,
                        status,
                        result_code,
                        result_message,
                        raw_payload
                    ) VALUES (
                        :order_id,
                        :gateway,
                        :gateway_payment_id,
                        :amount_cents,
                        :currency,
                        :status,
                        :result_code,
                        :result_message,
                        :raw_payload
                    )';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_id'          => $data['order_id'],
                ':gateway'           => $data['gateway'] ?? 'comgate',
                ':gateway_payment_id' => $data['gateway_payment_id'] ?? null,
                ':amount_cents'      => (int)$data['amount_cents'],
                ':currency'          => $data['currency'] ?? 'CZK',
                ':status'            => $data['status'] ?? 'pending',
                ':result_code'       => $data['result_code'] ?? null,
                ':result_message'    => $data['result_message'] ?? null,
                ':raw_payload'       => $data['raw_payload'] ?? null,
            ]);

            return [
                'success' => true,
                'id'      => (int)$this->db->lastInsertId(),
            ];
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'model'  => 'Transaction',
                'action' => 'create',
                'data'   => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Chyba při ukládání transakce.',
            ];
        }
    }

    public function getByOrderId(int $orderId): array
    {
        $sql = 'SELECT *
                FROM transactions
                WHERE order_id = :order_id
                ORDER BY created_at DESC, id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
