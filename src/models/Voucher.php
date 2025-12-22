<?php
// src/Models/Voucher.php

namespace Models;

use Core\Database;
use Helpers\Logger;
use PDO;

class Voucher
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Všechny aktivní vouchery pro výpis na webu (eshop).
     */
    public function getActive(): array
    {
        $sql = "SELECT id, name, slug, description, price_cents, currency, validity_months
                FROM vouchers
                WHERE is_active = 1
                ORDER BY price_cents ASC, id ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Najde voucher podle slugu (vybrání z FE).
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT *
                FROM vouchers
                WHERE slug = :slug AND is_active = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Načtení voucheru podle ID (pro admina, pro kódy atd.).
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT *
                FROM vouchers
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Admin: seznam všech voucherů (včetně neaktivních).
     */
    public function getAllAdmin(): array
    {
        $sql = "SELECT *
                FROM vouchers
                ORDER BY created_at DESC, id DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vytvoření voucheru (admin).
     */
    public function create(array $data): array
    {
        try {
            $sql = "INSERT INTO vouchers 
                    (name, slug, description, price_cents, currency, validity_months, is_active, created_by)
                    VALUES
                    (:name, :slug, :description, :price_cents, :currency, :validity_months, :is_active, :created_by)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name'            => $data['name'],
                ':slug'            => $data['slug'],
                ':description'     => $data['description'] ?? null,
                ':price_cents'     => (int)$data['price_cents'],
                ':currency'        => $data['currency'] ?? 'CZK',
                ':validity_months' => (int)($data['validity_months'] ?? 6),
                ':is_active'       => !empty($data['is_active']) ? 1 : 0,
                ':created_by'      => $data['created_by'] ?? null,
            ]);

            return [
                'success' => true,
                'id'      => (int)$this->db->lastInsertId(),
            ];
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'model' => 'Voucher',
                'action' => 'create',
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Chyba při vytváření voucheru.',
            ];
        }
    }

    /**
     * Update voucheru (admin).
     */
    public function updateAdmin(int $id, array $data): array
    {
        try {
            $sql = "UPDATE vouchers
                    SET name = :name,
                        slug = :slug,
                        description = :description,
                        price_cents = :price_cents,
                        currency = :currency,
                        validity_months = :validity_months,
                        is_active = :is_active
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'              => $id,
                ':name'            => $data['name'],
                ':slug'            => $data['slug'],
                ':description'     => $data['description'] ?? null,
                ':price_cents'     => (int)$data['price_cents'],
                ':currency'        => $data['currency'] ?? 'CZK',
                ':validity_months' => (int)($data['validity_months'] ?? 6),
                ':is_active'       => !empty($data['is_active']) ? 1 : 0,
            ]);

            return ['success' => true];
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'model'  => 'Voucher',
                'action' => 'update',
                'id'     => $id,
                'data'   => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Chyba při aktualizaci voucheru.',
            ];
        }
    }

    /**
     * “Smazání” – klidně jen deaktivace.
     */
    public function deactivate(int $id): array
    {
        try {
            $sql = "UPDATE vouchers
                    SET is_active = 0
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            return ['success' => true];
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'model'  => 'Voucher',
                'action' => 'deactivate',
                'id'     => $id,
            ]);

            return [
                'success' => false,
                'message' => 'Chyba při deaktivaci voucheru.',
            ];
        }
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        $sql = "SELECT id FROM vouchers WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($ignoreId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $ignoreId;
        }

        $sql .= " LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return (bool)$st->fetchColumn();
    }

    public function getListAdmin(int $limit = 200): array
    {
        $sql = "SELECT *
            FROM vouchers
            ORDER BY created_at DESC, id DESC
            LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
