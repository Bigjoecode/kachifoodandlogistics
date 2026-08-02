<?php
class User
{
    public static function find(int $id): ?array
    {
        return Db::first('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Db::first('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))]);
    }

    public static function emailTaken(string $email, ?int $ignoreId = null): bool
    {
        $sql    = 'SELECT id FROM users WHERE email = ?' . ($ignoreId ? ' AND id <> ?' : '');
        $params = $ignoreId ? [strtolower($email), $ignoreId] : [strtolower($email)];
        return Db::first($sql, $params) !== null;
    }

    public static function create(array $data): int
    {
        return Db::insert(
            'INSERT INTO users (name, email, phone, password_hash, role, company, address, city, state)
             VALUES (:name, :email, :phone, :password_hash, :role, :company, :address, :city, :state)',
            [
                'name'          => $data['name'],
                'email'         => strtolower(trim($data['email'])),
                'phone'         => $data['phone'] ?: null,
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role'          => $data['role'] ?? 'customer',
                'company'       => $data['company'] ?: null,
                'address'       => $data['address'] ?: null,
                'city'          => $data['city'] ?: null,
                'state'         => $data['state'] ?: null,
            ]
        );
    }

    public static function updateProfile(int $id, array $data): void
    {
        Db::run(
            'UPDATE users SET name = :name, phone = :phone, company = :company,
                    address = :address, city = :city, state = :state
             WHERE id = :id',
            [
                'name'    => $data['name'],
                'phone'   => $data['phone'] ?: null,
                'company' => $data['company'] ?: null,
                'address' => $data['address'] ?: null,
                'city'    => $data['city'] ?: null,
                'state'   => $data['state'] ?: null,
                'id'      => $id,
            ]
        );
    }

    public static function updatePassword(int $id, string $password): void
    {
        Db::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public static function setRole(int $id, string $role): void
    {
        Db::run('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        Db::run('UPDATE users SET is_active = ? WHERE id = ?', [(int) $active, $id]);
    }

    public static function verify(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return (int) $user['is_active'] === 1 ? $user : null;
    }

    /** Customer list for the back office, with lifetime order totals. */
    public static function paginate(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['role'])) {
            $where[]        = 'u.role = :role';
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['q'])) {
            $where[]     = '(u.name LIKE :q OR u.email LIKE :q OR u.company LIKE :q OR u.phone LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $perPage = max(1, (int) ($filters['per_page'] ?? 20));
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $total   = (int) Db::value("SELECT COUNT(*) FROM users u {$whereSql}", $params);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min($page, $pages);
        $offset  = ($page - 1) * $perPage;

        $rows = Db::all(
            "SELECT u.*,
                    (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                    (SELECT COALESCE(SUM(o.total), 0) FROM orders o
                      WHERE o.user_id = u.id AND o.status NOT IN ('cancelled','pending','quoted')) AS lifetime_value
             FROM users u {$whereSql}
             ORDER BY u.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    public static function countByRole(string $role): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM users WHERE role = ?', [$role]);
    }
}
