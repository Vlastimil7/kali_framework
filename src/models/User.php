<?php

namespace Models;

use Core\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Registrace nového uživatele
    public function register($userData)
    {
        // Validace dat
        if (
            empty($userData['email']) || empty($userData['password']) ||
            empty($userData['name']) || empty($userData['surname'])
        ) {
            return [
                'success' => false,
                'message' => 'Prosím vyplňte všechna povinná pole'
            ];
        }

        // Kontrola, zda email již existuje
        if ($this->emailExists($userData['email'])) {
            return [
                'success' => false,
                'message' => 'Email již existuje v databázi'
            ];
        }

        // Hashování hesla
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Výchozí hodnoty pro volitelná pole
        $phone = $userData['phone'] ?? '';
        $credit_balance = 0;
        $allow_debit = 0;
        $role = 'user';

        try {
            $sql = "INSERT INTO users (email, password, name, surname, phone, credit_balance, allow_debit, role, created_at) 
                    VALUES (:email, :password, :name, :surname, :phone, :credit_balance, :allow_debit, :role, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $userData['email'],
                ':password' => $hashedPassword,
                ':name' => $userData['name'],
                ':surname' => $userData['surname'],
                ':phone' => $phone,
                ':credit_balance' => $credit_balance,
                ':allow_debit' => $allow_debit,
                ':role' => $role
            ]);

            return [
                'success' => true,
                'message' => 'Uživatel byl úspěšně zaregistrován',
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage()
            ];
        }
    }

    // Kontrola, zda email již existuje
    private function emailExists($email)
    {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->rowCount() > 0;
    }

    // Přihlášení uživatele
    public function login($email, $password)
    {
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Zadejte prosím email a heslo'
            ];
        }

        try {
            $sql = "SELECT id, email, password, name, surname, role FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);

                // Ověření hesla
                if (password_verify($password, $user['password'])) {
                    // Neposíláme heslo zpět
                    unset($user['password']);

                    return [
                        'success' => true,
                        'message' => 'Přihlášení úspěšné',
                        'user' => $user
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Neplatné přihlašovací údaje'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Uživatel s tímto emailem nebyl nalezen'
                ];
            }
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage()
            ];
        }
    }

    // Získání uživatele podle ID
    public function getUserById($id)
    {
        $sql = "SELECT id, email, name, surname, phone, credit_balance, allow_debit, role, created_at 
                FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 1) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        return null;
    }

    // Aktualizace profilu uživatele
    public function updateProfile($userId, $userData)
    {
        // Generování dynamického SQL pro aktualizaci polí
        $updateFields = [];
        $params = [':id' => $userId];

        // Kontrola jednotlivých možných polí
        if (isset($userData['name']) && !empty($userData['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $userData['name'];
        }

        if (isset($userData['surname']) && !empty($userData['surname'])) {
            $updateFields[] = "surname = :surname";
            $params[':surname'] = $userData['surname'];
        }

        if (isset($userData['phone'])) {
            $updateFields[] = "phone = :phone";
            $params[':phone'] = $userData['phone'];
        }

        // Aktualizace hesla, pokud bylo poskytnuto
        if (isset($userData['password']) && !empty($userData['password'])) {
            $updateFields[] = "password = :password";
            $params[':password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        // Pokud nejsou žádná pole k aktualizaci, vrátíme
        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'Žádné údaje k aktualizaci'
            ];
        }

        try {
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Profil byl úspěšně aktualizován'
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage()
            ];
        }
    }

    // Získání všech uživatelů (admin funkce)
    public function getAllUsers()
    {
        $sql = "SELECT id, email, name, surname, phone, credit_balance, allow_debit, role, created_at FROM users";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Získání počtu uživatelů
    public function getUserCount()
    {
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql);
        $row = $result->fetch();
        return $row['count'];
    }

    // Aktualizace uživatele (pro admina)
    public function updateUser($userId, $userData)
    {
        // Generování dynamického SQL pro aktualizaci polí
        $updateFields = [];
        $params = [':id' => $userId];

        // Kontrola jednotlivých možných polí
        if (isset($userData['name']) && !empty($userData['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $userData['name'];
        }

        if (isset($userData['surname']) && !empty($userData['surname'])) {
            $updateFields[] = "surname = :surname";
            $params[':surname'] = $userData['surname'];
        }

        if (isset($userData['email']) && !empty($userData['email'])) {
            // Ověření, zda email již neexistuje u jiného uživatele
            $sql = "SELECT id FROM users WHERE email = :email AND id != :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $userData['email'], ':userId' => $userId]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'Email již používá jiný uživatel'
                ];
            }

            $updateFields[] = "email = :email";
            $params[':email'] = $userData['email'];
        }

        if (isset($userData['phone'])) {
            $updateFields[] = "phone = :phone";
            $params[':phone'] = $userData['phone'];
        }

        if (isset($userData['role'])) {
            $updateFields[] = "role = :role";
            $params[':role'] = $userData['role'];
        }

        if (isset($userData['credit_balance'])) {
            $updateFields[] = "credit_balance = :credit_balance";
            $params[':credit_balance'] = $userData['credit_balance'];
        }

        if (isset($userData['allow_debit'])) {
            $updateFields[] = "allow_debit = :allow_debit";
            $params[':allow_debit'] = $userData['allow_debit'];
        }

        // Aktualizace hesla, pokud bylo poskytnuto
        if (isset($userData['password']) && !empty($userData['password'])) {
            $updateFields[] = "password = :password";
            $params[':password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        // Pokud nejsou žádná pole k aktualizaci, vrátíme
        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'Žádné údaje k aktualizaci'
            ];
        }

        try {
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Uživatel byl úspěšně aktualizován'
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage()
            ];
        }
    }

    // Smazání uživatele
    public function deleteUser($userId)
    {
        try {
            // Kontrola, zda uživatel nemá objednávky
            $sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':userId' => $userId]);
            $row = $stmt->fetch();

            if ($row['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Uživatel má objednávky a nemůže být smazán'
                ];
            }

            // Smazání uživatele
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $userId]);

            return [
                'success' => true,
                'message' => 'Uživatel byl úspěšně smazán'
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage()
            ];
        }
    }

    // Aktualizace kreditu uživatele
    public function updateCredit($userId, $amount, $paymentMethod = null, $orderId = null)
    {
        try {
            $this->db->beginTransaction();

            // Získání aktuálního stavu kreditu
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new \Exception('Uživatel nebyl nalezen');
            }

            // Výpočet nového stavu kreditu
            $newBalance = $user['credit_balance'] + $amount;

            // Kontrola debetu, pokud je částka záporná
            if ($amount < 0 && $newBalance < 0 && !$user['allow_debit']) {
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Nedostatečný kredit a uživatel nemá povolený debet'
                ];
            }

            // Aktualizace kreditu
            $sql = "UPDATE users SET credit_balance = :balance WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':balance' => $newBalance,
                ':id' => $userId
            ]);

            // Určení typu transakce
            $transactionType = $amount > 0 ? 'dobití' : 'odečtení';
            $status = 'completed';

            // Vložení záznamu o transakci pouze při dobití nebo když orderId není specifikováno
            // (aby nedošlo k duplicitním záznamům při tvorbě objednávek)
            if ($transactionType === 'dobití' || ($transactionType === 'odečtení' && $orderId === null)) {
                $transactionSql = "INSERT INTO credit_transactions 
                              (user_id, order_id, amount, transaction_type, payment_method, transaction_date, status) 
                              VALUES 
                              (:user_id, :order_id, :amount, :type, :method, NOW(), :status)";

                $transStmt = $this->db->prepare($transactionSql);
                $transStmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                $transStmt->bindValue(':order_id', $orderId ? : 0, \PDO::PARAM_INT);
                $transStmt->bindValue(':amount', abs($amount), \PDO::PARAM_STR); // Ukládáme absolutní hodnotu
                $transStmt->bindValue(':type', $transactionType, \PDO::PARAM_STR);
                $transStmt->bindValue(':method', $paymentMethod, \PDO::PARAM_STR);
                $transStmt->bindValue(':status', $status, \PDO::PARAM_STR);
                $transStmt->execute();
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => $transactionType === 'dobití' ? 'Kredit byl úspěšně dobit' : 'Kredit byl úspěšně odečten',
                'new_balance' => $newBalance
            ];
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            error_log('Error in updateCredit: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Chyba při aktualizaci kreditu: ' . $e->getMessage()
            ];
        }
    }
}
