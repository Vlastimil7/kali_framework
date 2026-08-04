<?php

// src/Models/ContactRequest.php

namespace Models;

use Core\Database;
use Helpers\Logger;

class ContactRequest
{
    private Database $db;
    private const TABLE_NAME = 'web_form_request';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): array
    {
        try {
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (
                        first_name,
                        last_name,
                        email,
                        phone,
                        category,
                        budget,
                        note,
                        status,
                        ip_address,
                        user_agent,
                        gdpr
                    ) VALUES (
                        :first_name,
                        :last_name,
                        :email,
                        :phone,
                        :category,
                        :budget,
                        :note,
                        :status,
                        :ip_address,
                        :user_agent,
                        :gdpr
                    )';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name'  => $data['last_name'],
                ':email'      => $data['email'],
                ':phone'      => $data['phone'] ?? '',
                ':category'   => $data['category'] ?? '',
                ':budget'     => $data['budget'] ?? '',
                ':note'       => $data['note'] ?? '',
                ':status'     => $data['status'] ?? 'new',
                ':ip_address' => $data['ip_address'] ?? null,
                ':user_agent' => $data['user_agent'] ?? null,
                ':gdpr'       => $data['gdpr'] ?? 0,
            ]);

            return [
                'success' => true,
                'id'      => (int)$this->db->lastInsertId(),
            ];
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'model'  => 'ContactRequest',
                'action' => 'create',
                'data'   => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Chyba při ukládání poptávky.',
            ];
        }
    }
}
