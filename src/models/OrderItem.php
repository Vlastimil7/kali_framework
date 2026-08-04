<?php

namespace Models;

use Core\Database;
use PDO;

class OrderItem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByOrderId(int $orderId): array
    {
        $sql = 'SELECT
                oi.*,
                v.name AS voucher_name,
                v.slug AS voucher_slug
            FROM order_items oi
            LEFT JOIN vouchers v ON v.id = oi.voucher_id
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * $data: order_id, voucher_id, quantity, unit_price_cents, total_price_cents, recipient_name?, note?
     * Vrací order_item_id.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO order_items (
                order_id,
                voucher_id,
                quantity,
                unit_price_cents,
                total_price_cents,
                recipient_name,
                note,
                voucher_name_snapshot
            ) VALUES (
                :order_id,
                :voucher_id,
                :quantity,
                :unit_price_cents,
                :total_price_cents,
                :recipient_name,
                :note,
                :voucher_name_snapshot
            )';


        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':order_id' => (int)$data['order_id'],
            ':voucher_id' => (int)$data['voucher_id'],
            ':quantity' => (int)$data['quantity'],
            ':unit_price_cents' => (int)$data['unit_price_cents'],
            ':total_price_cents' => (int)$data['total_price_cents'],
            ':recipient_name' => $data['recipient_name'] ?? null,
            ':note' => $data['note'] ?? null,
            ':voucher_name_snapshot' => $data['voucher_name_snapshot'] ?? null,

        ]);

        return (int)$this->db->lastInsertId();
    }
}
