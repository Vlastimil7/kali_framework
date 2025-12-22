<?php

namespace Models;

use Core\Database;
use PDO;
use Helpers\Logger;

class VoucherCode
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------
    // READ
    // -------------------------

    public function countByOrderId(int $orderId): int
    {
        $st = $this->db->prepare("SELECT COUNT(*) FROM voucher_codes WHERE order_id = :oid");
        $st->execute([':oid' => $orderId]);
        return (int)$st->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM voucher_codes WHERE id = :id LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByOrderId(int $orderId): array
    {
        $sql = "SELECT vc.*, v.name AS voucher_name
                FROM voucher_codes vc
                JOIN vouchers v ON v.id = vc.voucher_id
                WHERE vc.order_id = :order_id
                ORDER BY vc.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getByCode(string $code): ?array
    {
        $sql = "SELECT vc.*,
                       v.name AS voucher_name,
                       v.slug AS voucher_slug,
                       o.order_number,
                       o.status AS order_status,
                       o.billing_name,
                       o.billing_email,
                       o.billing_phone
                FROM voucher_codes vc
                JOIN vouchers v ON v.id = vc.voucher_id
                JOIN orders o ON o.id = vc.order_id
                WHERE vc.code = :code
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getVerifyDataByCode(string $code): ?array
    {
        // verify view bere stejné údaje
        return $this->getByCode($code);
    }

    // -------------------------
    // CREATE (manual / exchange / etc.)
    // -------------------------

    public function create(array $data): array
    {
        try {
            $sql = "INSERT INTO voucher_codes (
                        order_id, order_item_id, voucher_id,
                        code, valid_from, valid_to, status,
                        exchanged_from_id,
                        exchanged_at, exchanged_by, exchange_reason
                    ) VALUES (
                        :order_id, :order_item_id, :voucher_id,
                        :code, :valid_from, :valid_to, :status,
                        :exchanged_from_id,
                        :exchanged_at, :exchanged_by, :exchange_reason
                    )";

            $st = $this->db->prepare($sql);
            $st->execute([
                ':order_id'         => (int)$data['order_id'],
                ':order_item_id'    => (int)$data['order_item_id'],
                ':voucher_id'       => (int)$data['voucher_id'],
                ':code'             => (string)$data['code'],
                ':valid_from'       => (string)$data['valid_from'],
                ':valid_to'         => (string)$data['valid_to'],
                ':status'           => (string)($data['status'] ?? 'pending_payment'),

                ':exchanged_from_id' => $data['exchanged_from_id'] ?? null,
                ':exchanged_at'     => $data['exchanged_at'] ?? null,
                ':exchanged_by'     => $data['exchanged_by'] ?? null,
                ':exchange_reason'  => $data['exchange_reason'] ?? null,
            ]);

            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Throwable $e) {
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'create', 'data' => $data]);
            return ['success' => false, 'message' => 'Chyba při vytváření kódu voucheru.'];
        }
    }

    // -------------------------
    // GENERATION FOR PAID ORDER
    // -------------------------

    public function ensureGeneratedForPaidOrder(int $orderId): array
    {
        try {
            // idempotence
            if ($this->countByOrderId($orderId) > 0) {
                return ['success' => true, 'skipped' => true];
            }

            $sql = "SELECT
                        oi.id AS order_item_id,
                        oi.voucher_id,
                        oi.quantity,
                        v.validity_months
                    FROM order_items oi
                    JOIN vouchers v ON v.id = oi.voucher_id
                    WHERE oi.order_id = :oid
                    ORDER BY oi.id ASC";

            $st = $this->db->prepare($sql);
            $st->execute([':oid' => $orderId]);
            $items = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (!$items) {
                return ['success' => false, 'message' => 'Objednávka nemá položky (order_items).'];
            }

            $created = 0;
            $today = new \DateTimeImmutable('today');

            $canTx = method_exists($this->db, 'beginTransaction');
            if ($canTx) $this->db->beginTransaction();

            foreach ($items as $it) {
                $qty = (int)($it['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $voucherId   = (int)$it['voucher_id'];
                $orderItemId = (int)$it['order_item_id'];
                $months      = max(1, (int)($it['validity_months'] ?? 6));

                $validFrom = $today->format('Y-m-d');
                $validTo   = $today->modify('+' . $months . ' months')->format('Y-m-d');

                for ($i = 0; $i < $qty; $i++) {
                    $code = $this->generateCode($orderId);

                    $ins = "INSERT INTO voucher_codes
                                (order_id, order_item_id, voucher_id, code, valid_from, valid_to, status)
                            VALUES
                                (:order_id, :order_item_id, :voucher_id, :code, :valid_from, :valid_to, 'active')";

                    $stmtIns = $this->db->prepare($ins);

                    $tries = 0;
                    while (true) {
                        try {
                            $stmtIns->execute([
                                ':order_id'      => $orderId,
                                ':order_item_id' => $orderItemId,
                                ':voucher_id'    => $voucherId,
                                ':code'          => $code,
                                ':valid_from'    => $validFrom,
                                ':valid_to'      => $validTo,
                            ]);
                            $created++;
                            break;
                        } catch (\PDOException $e) {
                            $tries++;
                            if ($tries >= 5) throw $e;
                            $code = $this->generateCode($orderId);
                        }
                    }
                }
            }

            if ($canTx) $this->db->commit();

            if ($created === 0) {
                return ['success' => false, 'message' => 'Nevznikl žádný kód (quantity je 0?).'];
            }

            return ['success' => true, 'created' => $created];
        } catch (\Throwable $e) {
            if (method_exists($this->db, 'rollBack')) {
                try {
                    $this->db->rollBack();
                } catch (\Throwable $ignore) {
                }
            }
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'ensureGeneratedForPaidOrder', 'order_id' => $orderId]);
            return ['success' => false, 'message' => 'Chyba při generování voucher kódů.'];
        }
    }

    private function generateCode(int $orderId): string
    {
        return strtoupper(bin2hex(random_bytes(4))) . '-' . $orderId;
    }

    // -------------------------
    // ACTIONS BY CODE (verify page buttons)
    // -------------------------

    public function redeemByCode(string $code, int $adminId, string $note = ''): array
    {
        try {
            $sql = "UPDATE voucher_codes vc
                    SET vc.status = 'redeemed',
                        vc.redeemed_at = NOW(),
                        vc.redeemed_by = :admin_id,
                        vc.redemption_note = :note
                    WHERE vc.code = :code
                      AND vc.status = 'active'
                      AND (vc.valid_to IS NULL OR vc.valid_to >= CURDATE())
                      AND EXISTS (
                          SELECT 1
                          FROM orders o
                          WHERE o.id = vc.order_id
                            AND o.status = 'paid'
                      )";

            $st = $this->db->prepare($sql);
            $st->execute([
                ':admin_id' => $adminId ?: null,
                ':note'     => ($note !== '' ? $note : null),
                ':code'     => $code,
            ]);

        if ($st->rowCount() !== 1) {

            // pokus se označit jako expired (nevadí když nic neupdatuje)
            $sqlExpire = "
                UPDATE voucher_codes
                SET status = 'expired'
                WHERE code = :code
                AND status = 'active'
                AND valid_to IS NOT NULL
                AND valid_to < CURDATE()
            ";
            $this->db->prepare($sqlExpire)->execute([':code' => $code]);

            return [
                'success' => false,
                'message' => 'Nelze uplatnit (neplacené / expirované / už uplatněné / neaktivní).'
            ];
        }

            return ['success' => true];
        } catch (\Throwable $e) {
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'redeemByCode', 'code' => $code]);
            return ['success' => false, 'message' => 'Chyba při uplatnění.'];
        }
    }

    public function voidByCode(string $code, int $adminId, string $reason = '', string $newStatus = 'canceled'): array
    {
        try {
            if (!in_array($newStatus, ['canceled', 'refunded'], true)) {
                $newStatus = 'canceled';
            }

            // nešahat na redeemed
            $sql = "UPDATE voucher_codes vc
                    SET vc.status = :st,
                        vc.voided_at = NOW(),
                        vc.voided_by = :admin_id,
                        vc.void_reason = :reason
                    WHERE vc.code = :code
                      AND vc.status IN ('active','pending_payment','expired')";

            $st = $this->db->prepare($sql);
            $st->execute([
                ':st'       => $newStatus,
                ':admin_id' => $adminId ?: null,
                ':reason'   => ($reason !== '' ? $reason : null),
                ':code'     => $code,
            ]);

            if ($st->rowCount() !== 1) {
                return ['success' => false, 'message' => 'Nelze zrušit/refundovat (už uplatněno nebo už zrušeno).'];
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'voidByCode', 'code' => $code]);
            return ['success' => false, 'message' => 'Chyba při stornu/refundu kódu.'];
        }
    }

    public function exchangeByCode(string $code, int $adminId, string $reason = ''): array
    {
        try {
            $old = $this->getByCode($code);
            if (!$old) return ['success' => false, 'message' => 'Kód nenalezen.'];

            if (($old['status'] ?? '') !== 'active') {
                return ['success' => false, 'message' => 'Vyměnit lze jen aktivní kód.'];
            }

            // idempotence
            if (!empty($old['exchanged_at'])) {
                return ['success' => false, 'message' => 'Tento kód už byl vyměněn.'];
            }

            $canTx = method_exists($this->db, 'beginTransaction');
            if ($canTx) $this->db->beginTransaction();

            $newCode = $this->generateCode((int)$old['order_id']);

            // 1) new code row
            $ins = "INSERT INTO voucher_codes
                        (order_id, order_item_id, voucher_id, code, valid_from, valid_to, status,
                         exchanged_from_id, exchanged_at, exchanged_by, exchange_reason)
                    VALUES
                        (:order_id, :order_item_id, :voucher_id, :code, :valid_from, :valid_to, 'active',
                         :from_id, NOW(), :by, :reason)";

            $stIns = $this->db->prepare($ins);

            $tries = 0;
            while (true) {
                try {
                    $stIns->execute([
                        ':order_id'      => (int)$old['order_id'],
                        ':order_item_id' => (int)$old['order_item_id'],
                        ':voucher_id'    => (int)$old['voucher_id'],
                        ':code'          => $newCode,
                        ':valid_from'    => (string)$old['valid_from'],
                        ':valid_to'      => (string)$old['valid_to'],
                        ':from_id'       => (int)$old['id'],
                        ':by'            => $adminId ?: null,
                        ':reason'        => ($reason !== '' ? $reason : null),
                    ]);
                    $newId = (int)$this->db->lastInsertId();
                    break;
                } catch (\PDOException $e) {
                    $tries++;
                    if ($tries >= 5) throw $e;
                    $newCode = $this->generateCode((int)$old['order_id']);
                }
            }

            // 2) old becomes canceled
            $upd = "UPDATE voucher_codes
                    SET status = 'canceled',
                        exchanged_at = NOW(),
                        exchanged_by = :by,
                        exchange_reason = :reason
                    WHERE id = :id
                      AND status = 'active'";

            $stUpd = $this->db->prepare($upd);
            $stUpd->execute([
                ':by'     => $adminId ?: null,
                ':reason' => ($reason !== '' ? $reason : null),
                ':id'     => (int)$old['id'],
            ]);

            if ($stUpd->rowCount() !== 1) {
                if ($canTx) $this->db->rollBack();
                return ['success' => false, 'message' => 'Nepodařilo se zneplatnit původní kód.'];
            }

            if ($canTx) $this->db->commit();

            return ['success' => true, 'new_id' => $newId, 'new_code' => $newCode];
        } catch (\Throwable $e) {
            if (method_exists($this->db, 'rollBack')) {
                try {
                    $this->db->rollBack();
                } catch (\Throwable $ignore) {
                }
            }
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'exchangeByCode', 'code' => $code]);
            return ['success' => false, 'message' => 'Chyba při výměně kódu.'];
        }
    }

    // -------------------------
    // ORDER-WIDE HELPERS (for OrdersController)
    // -------------------------

    public function hasRedeemedByOrderId(int $orderId): bool
    {
        $st = $this->db->prepare("SELECT 1 FROM voucher_codes WHERE order_id = :oid AND status = 'redeemed' LIMIT 1");
        $st->execute([':oid' => $orderId]);
        return (bool)$st->fetchColumn();
    }

    public function voidAllByOrderId(int $orderId, string $newStatus, int $adminId, string $reason = ''): array
    {
        try {
            if (!in_array($newStatus, ['canceled', 'refunded'], true)) {
                return ['success' => false, 'message' => 'Neplatný status pro zrušení voucherů.'];
            }

            // nešahat na redeemed
            $sql = "UPDATE voucher_codes
                    SET status = :st,
                        voided_at = NOW(),
                        voided_by = :by,
                        void_reason = :reason
                    WHERE order_id = :oid
                      AND status IN ('pending_payment','active','expired')";

            $st = $this->db->prepare($sql);
            $st->execute([
                ':st'     => $newStatus,
                ':by'     => $adminId ?: null,
                ':reason' => ($reason !== '' ? $reason : null),
                ':oid'    => $orderId,
            ]);

            return ['success' => true, 'affected' => $st->rowCount()];
        } catch (\Throwable $e) {
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'voidAllByOrderId', 'order_id' => $orderId]);
            return ['success' => false, 'message' => 'Chyba při zrušení voucherů objednávky.'];
        }
    }

    public function expireAllByOrderId(int $orderId): array
    {
        try {
            $sql = "UPDATE voucher_codes
                    SET status = 'expired'
                    WHERE order_id = :oid
                      AND status IN ('pending_payment','active')";

            $st = $this->db->prepare($sql);
            $st->execute([':oid' => $orderId]);

            return ['success' => true, 'affected' => $st->rowCount()];
        } catch (\Throwable $e) {
            Logger::exception($e, ['model' => 'VoucherCode', 'action' => 'expireAllByOrderId', 'order_id' => $orderId]);
            return ['success' => false, 'message' => 'Chyba při expiraci voucherů objednávky.'];
        }
    }
}
