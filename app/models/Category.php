<?php
class Category
{
    public static function all(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return Db::all("SELECT * FROM categories {$where} ORDER BY sort_order, name");
    }

    /** Categories with a live product count, for menus and the catalogue sidebar. */
    public static function withCounts(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE c.is_active = 1' : '';
        return Db::all(
            "SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
             {$where}
             GROUP BY c.id
             ORDER BY c.sort_order, c.name"
        );
    }

    public static function find(int $id): ?array
    {
        return Db::first('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Db::first('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    }

    public static function create(array $data): int
    {
        return Db::insert(
            'INSERT INTO categories (name, slug, description, icon, sort_order, is_active)
             VALUES (:name, :slug, :description, :icon, :sort_order, :is_active)',
            self::bind($data)
        );
    }

    public static function update(int $id, array $data): void
    {
        $params       = self::bind($data);
        $params['id'] = $id;
        Db::run(
            'UPDATE categories SET name = :name, slug = :slug, description = :description,
                    icon = :icon, sort_order = :sort_order, is_active = :is_active
             WHERE id = :id',
            $params
        );
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM categories WHERE id = ?', [$id]);
    }

    /** Ensures a slug is unique, appending -2, -3 ... when it is taken. */
    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i    = 1;
        while (true) {
            $sql    = 'SELECT id FROM categories WHERE slug = ?' . ($ignoreId ? ' AND id <> ?' : '');
            $params = $ignoreId ? [$slug, $ignoreId] : [$slug];
            if (!Db::first($sql, $params)) {
                return $slug;
            }
            $slug = $base . '-' . (++$i);
        }
    }

    private static function bind(array $d): array
    {
        return [
            'name'        => $d['name'],
            'slug'        => $d['slug'],
            'description' => $d['description'] ?: null,
            'icon'        => $d['icon'] ?: null,
            'sort_order'  => (int) ($d['sort_order'] ?? 0),
            'is_active'   => (int) (bool) ($d['is_active'] ?? 1),
        ];
    }
}
