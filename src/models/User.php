<?php

namespace Models;

use Core\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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
                'message' => 'Prosím vyplňte všechna povinná pole',
            ];
        }

        // Kontrola, zda email již existuje
        if ($this->emailExists($userData['email'])) {
            return [
                'success' => false,
                'message' => 'Email již existuje v databázi',
            ];
        }

        // Hashování hesla
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Výchozí hodnoty pro volitelná pole
        $phone = $userData['phone'] ?? '';
        $role = $userData['role'] ?? 'user';

        try {
            $sql = 'INSERT INTO users (email, password, name, surname, phone, role, created_at)
                    VALUES (:email, :password, :name, :surname, :phone, :role, NOW())';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $userData['email'],
                ':password' => $hashedPassword,
                ':name' => $userData['name'],
                ':surname' => $userData['surname'],
                ':phone' => $phone,
                ':role' => $role,
            ]);

            return [
                'success' => true,
                'message' => 'Uživatel byl úspěšně zaregistrován',
                'user_id' => $this->db->lastInsertId(),
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    // Kontrola, zda email již existuje
    private function emailExists($email)
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
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
                'message' => 'Zadejte prosím email a heslo',
            ];
        }

        try {
            $sql = 'SELECT id, email, password, name, surname, role FROM users WHERE email = :email';
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
                        'user' => $user,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Neplatné přihlašovací údaje',
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Uživatel s tímto emailem nebyl nalezen',
                ];
            }
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    // Získání uživatele podle ID
    public function getUserById($id)
    {
        $sql = 'SELECT id, email, name, surname, phone, role, created_at
                FROM users WHERE id = :id';
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
            $updateFields[] = 'name = :name';
            $params[':name'] = $userData['name'];
        }

        if (isset($userData['surname']) && !empty($userData['surname'])) {
            $updateFields[] = 'surname = :surname';
            $params[':surname'] = $userData['surname'];
        }

        if (isset($userData['phone'])) {
            $updateFields[] = 'phone = :phone';
            $params[':phone'] = $userData['phone'];
        }

        // Aktualizace hesla, pokud bylo poskytnuto
        if (isset($userData['password']) && !empty($userData['password'])) {
            $updateFields[] = 'password = :password';
            $params[':password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        // Pokud nejsou žádná pole k aktualizaci, vrátíme
        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'Žádné údaje k aktualizaci',
            ];
        }

        try {
            $sql = 'UPDATE users SET ' . implode(', ', $updateFields) . ' WHERE id = :id';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Profil byl úspěšně aktualizován',
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    // Získání všech uživatelů (admin funkce)
    public function getAllUsers()
    {
        $sql = 'SELECT id, email, name, surname, phone, role, created_at FROM users';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Získání počtu uživatelů
    public function getUserCount()
    {
        $sql = 'SELECT COUNT(*) as count FROM users';
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
            $updateFields[] = 'name = :name';
            $params[':name'] = $userData['name'];
        }

        if (isset($userData['surname']) && !empty($userData['surname'])) {
            $updateFields[] = 'surname = :surname';
            $params[':surname'] = $userData['surname'];
        }

        if (isset($userData['email']) && !empty($userData['email'])) {
            // Ověření, zda email již neexistuje u jiného uživatele
            $sql = 'SELECT id FROM users WHERE email = :email AND id != :userId';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $userData['email'], ':userId' => $userId]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'Email již používá jiný uživatel',
                ];
            }

            $updateFields[] = 'email = :email';
            $params[':email'] = $userData['email'];
        }

        if (isset($userData['phone'])) {
            $updateFields[] = 'phone = :phone';
            $params[':phone'] = $userData['phone'];
        }

        if (isset($userData['role'])) {
            $updateFields[] = 'role = :role';
            $params[':role'] = $userData['role'];
        }


        // Aktualizace hesla, pokud bylo poskytnuto
        if (isset($userData['password']) && !empty($userData['password'])) {
            $updateFields[] = 'password = :password';
            $params[':password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        // Pokud nejsou žádná pole k aktualizaci, vrátíme
        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'Žádné údaje k aktualizaci',
            ];
        }

        try {
            $sql = 'UPDATE users SET ' . implode(', ', $updateFields) . ' WHERE id = :id';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Uživatel byl úspěšně aktualizován',
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    // Smazání uživatele
    public function deleteUser($userId)
    {
        try {
            // // Kontrola, zda uživatel nemá objednávky
            // $sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = :userId";
            // $stmt = $this->db->prepare($sql);
            // $stmt->execute([':userId' => $userId]);
            // $row = $stmt->fetch();

            // if ($row['count'] > 0) {
            //     return [
            //         'success' => false,
            //         'message' => 'Uživatel má objednávky a nemůže být smazán'
            //     ];
            // }

            // Smazání uživatele
            $sql = 'DELETE FROM users WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $userId]);

            return [
                'success' => true,
                'message' => 'Uživatel byl úspěšně smazán',
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Získání uživatele pomocí emailu
     */
    public function getUserByEmail($email)
    {
        $sql = 'SELECT id, email, name, surname, phone, role, created_at
            FROM users WHERE email = :email';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() === 1) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        return null;
    }

    /**
     * Vytvoření tokenu pro reset hesla
     */
    public function createPasswordResetToken($email)
    {
        // Kontrola, zda email existuje
        if (!$this->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Email nebyl nalezen v naší databázi',
            ];
        }

        // Generování unikátního tokenu
        $token = bin2hex(random_bytes(32));

        // Nastavení expirace tokenu (například 1 hodina)
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        try {
            // Nejprve deaktivujeme všechny staré tokeny pro tento email
            $deactivateSql = 'UPDATE password_resets SET used = 1 WHERE email = :email';
            $stmt = $this->db->prepare($deactivateSql);
            $stmt->execute([':email' => $email]);

            // Vložení nového tokenu
            $sql = 'INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires' => $expires,
            ]);

            // Získání jména uživatele pro email
            $user = $this->getUserByEmail($email);
            $userName = $user ? $user['name'] : '';

            return [
                'success' => true,
                'token' => $token,
                'email' => $email,
                'userName' => $userName,
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ověření tokenu pro reset hesla
     */
    public function verifyPasswordResetToken($token)
    {
        try {
            $sql = 'SELECT * FROM password_resets WHERE token = :token AND used = 0 AND expires_at > NOW()';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);

            if ($stmt->rowCount() === 1) {
                return [
                    'success' => true,
                    'reset_data' => $stmt->fetch(\PDO::FETCH_ASSOC),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Neplatný nebo expirovaný token pro reset hesla',
                ];
            }
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reset hesla
     */
    public function resetPassword($token, $newPassword)
    {
        try {
            $this->db->beginTransaction();

            // Ověření tokenu
            $tokenResult = $this->verifyPasswordResetToken($token);
            if (!$tokenResult['success']) {
                $this->db->rollback();
                return $tokenResult;
            }

            $resetData = $tokenResult['reset_data'];
            $email = $resetData['email'];

            // Hashování nového hesla
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Aktualizace hesla
            $updateSql = 'UPDATE users SET password = :password WHERE email = :email';
            $stmt = $this->db->prepare($updateSql);
            $stmt->execute([
                ':password' => $hashedPassword,
                ':email' => $email,
            ]);

            // Označení tokenu jako použitého
            $tokenSql = 'UPDATE password_resets SET used = 1 WHERE token = :token';
            $stmt = $this->db->prepare($tokenSql);
            $stmt->execute([':token' => $token]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Heslo bylo úspěšně změněno',
            ];
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }

    // Vytvoření uživatele z Google účtu
    public function createGoogleUser(array $googleUser): array
    {
        $email = trim((string)($googleUser['email'] ?? ''));
        $name = trim((string)($googleUser['given_name'] ?? ''));
        $surname = trim((string)($googleUser['family_name'] ?? ''));
        $picture = trim((string)($googleUser['picture'] ?? ''));

        if ($email === '') {
            return [
                'success' => false,
                'message' => 'Google účet nevrátil email.',
            ];
        }

        if ($this->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Uživatel s tímto emailem už existuje.',
            ];
        }

        $randomPasswordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        try {
            $sql = 'INSERT INTO users (email, password, name, surname, phone, role, created_at)
                VALUES (:email, :password, :name, :surname, :phone, :role, NOW())';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':password' => $randomPasswordHash,
                ':name' => $name !== '' ? $name : 'Google',
                ':surname' => $surname !== '' ? $surname : 'User',
                ':phone' => '',
                ':role' => 'user',
            ]);

            return [
                'success' => true,
                'message' => 'Google uživatel byl vytvořen.',
                'user_id' => (int)$this->db->lastInsertId(),
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba databáze: ' . $e->getMessage(),
            ];
        }
    }
}
