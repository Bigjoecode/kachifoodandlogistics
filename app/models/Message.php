<?php
class Message
{
    public static function create(array $data): int
    {
        return Db::insert(
            'INSERT INTO contact_messages (name, email, phone, subject, message)
             VALUES (:name, :email, :phone, :subject, :message)',
            [
                'name'    => $data['name'],
                'email'   => $data['email'],
                'phone'   => $data['phone'] ?: null,
                'subject' => $data['subject'] ?: null,
                'message' => $data['message'],
            ]
        );
    }

    public static function find(int $id): ?array
    {
        return Db::first('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function paginate(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (($filters['status'] ?? '') === 'unread') {
            $where[] = 'is_read = 0';
        } elseif (($filters['status'] ?? '') === 'read') {
            $where[] = 'is_read = 1';
        }
        if (!empty($filters['q'])) {
            $where[]     = '(name LIKE :q OR email LIKE :q OR subject LIKE :q OR message LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $total   = (int) Db::value("SELECT COUNT(*) FROM contact_messages {$whereSql}", $params);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min($page, $pages);
        $offset  = ($page - 1) * $perPage;

        $rows = Db::all(
            "SELECT * FROM contact_messages {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    public static function markRead(int $id, bool $read = true): void
    {
        Db::run('UPDATE contact_messages SET is_read = ? WHERE id = ?', [(int) $read, $id]);
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function unreadCount(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
    }
}
