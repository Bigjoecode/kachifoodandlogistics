<?php
class Product
{
    /**
     * Price for a given line quantity. Wholesale wins once the quantity reaches
     * the product's wholesale threshold; below that, a sale price beats retail.
     */
    public static function effectivePrice(array $product, int $quantity = 1): float
    {
        if (self::qualifiesForWholesale($product, $quantity)) {
            return (float) $product['wholesale_price'];
        }

        $retail = (float) $product['retail_price'];
        $sale   = $product['sale_price'] !== null ? (float) $product['sale_price'] : null;

        return ($sale !== null && $sale > 0 && $sale < $retail) ? $sale : $retail;
    }

    public static function retailPrice(array $product): float
    {
        return self::effectivePrice($product, 1);
    }

    public static function hasWholesale(array $product): bool
    {
        return $product['wholesale_price'] !== null
            && (float) $product['wholesale_price'] > 0
            && (float) $product['wholesale_price'] < (float) $product['retail_price'];
    }

    public static function qualifiesForWholesale(array $product, int $quantity): bool
    {
        return self::hasWholesale($product) && $quantity >= max(1, (int) $product['wholesale_min_qty']);
    }

    /** True when the shown price is below the plain retail price. */
    public static function isOnSale(array $product): bool
    {
        return self::retailPrice($product) < (float) $product['retail_price'];
    }

    /** What a buyer saves per unit by ordering at wholesale volume. */
    public static function wholesaleSaving(array $product): float
    {
        return self::hasWholesale($product)
            ? (float) $product['retail_price'] - (float) $product['wholesale_price']
            : 0.0;
    }

    public static function find(int $id): ?array
    {
        return Db::first(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?',
            [$id]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Db::first(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.is_active = 1',
            [$slug]
        );
    }

    /** Rows keyed by id — used by the cart to resolve many ids at once. */
    public static function findMany(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = Db::all(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id IN ({$placeholders}) AND p.is_active = 1",
            $ids
        );
        return array_column($rows, null, 'id');
    }

    public static function featured(int $limit = 6): array
    {
        return Db::all(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1 AND p.is_featured = 1
             ORDER BY p.updated_at DESC LIMIT ' . (int) $limit
        );
    }

    public static function related(array $product, int $limit = 4): array
    {
        return Db::all(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1 AND p.id <> ? AND p.category_id <=> ?
             ORDER BY RAND() LIMIT ' . (int) $limit,
            [(int) $product['id'], $product['category_id']]
        );
    }

    /**
     * Catalogue query.
     * $filters: category (slug), q, sort, min, max, page, per_page, include_inactive
     */
    public static function paginate(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (empty($filters['include_inactive'])) {
            $where[] = 'p.is_active = 1';
        }
        if (!empty($filters['category'])) {
            $where[]            = 'c.slug = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['category_id'])) {
            $where[]               = 'p.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }
        if (!empty($filters['q'])) {
            $where[]     = '(p.name LIKE :q OR p.summary LIKE :q OR p.sku LIKE :q OR p.origin LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if (isset($filters['min']) && $filters['min'] !== '' && $filters['min'] !== null) {
            $where[]       = 'p.retail_price >= :min';
            $params['min'] = (float) $filters['min'];
        }
        if (isset($filters['max']) && $filters['max'] !== '' && $filters['max'] !== null) {
            $where[]       = 'p.retail_price <= :max';
            $params['max'] = (float) $filters['max'];
        }
        if (($filters['stock'] ?? '') === 'in') {
            $where[] = 'p.stock_qty > 0';
        } elseif (($filters['stock'] ?? '') === 'out') {
            $where[] = 'p.stock_qty <= 0';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderSql = [
            'price_asc'  => 'p.retail_price ASC',
            'price_desc' => 'p.retail_price DESC',
            'name'       => 'p.name ASC',
            'oldest'     => 'p.created_at ASC',
            'stock'      => 'p.stock_qty ASC',
        ][$filters['sort'] ?? ''] ?? 'p.is_featured DESC, p.created_at DESC';

        $perPage = max(1, (int) ($filters['per_page'] ?? PER_PAGE));
        $page    = max(1, (int) ($filters['page'] ?? 1));

        $total = (int) Db::value(
            "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id {$whereSql}",
            $params
        );
        $pages  = max(1, (int) ceil($total / $perPage));
        $page   = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $rows = Db::all(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             {$whereSql} ORDER BY {$orderSql} LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page, 'per_page' => $perPage];
    }

    public static function create(array $data): int
    {
        return Db::insert(
            'INSERT INTO products (category_id, name, slug, sku, summary, description, origin, unit,
                                   retail_price, wholesale_price, wholesale_min_qty, sale_price,
                                   min_order, stock_qty, image, is_featured, is_active)
             VALUES (:category_id, :name, :slug, :sku, :summary, :description, :origin, :unit,
                     :retail_price, :wholesale_price, :wholesale_min_qty, :sale_price,
                     :min_order, :stock_qty, :image, :is_featured, :is_active)',
            self::bind($data)
        );
    }

    public static function update(int $id, array $data): void
    {
        $params       = self::bind($data);
        $params['id'] = $id;
        Db::run(
            'UPDATE products SET category_id = :category_id, name = :name, slug = :slug, sku = :sku,
                    summary = :summary, description = :description, origin = :origin, unit = :unit,
                    retail_price = :retail_price, wholesale_price = :wholesale_price,
                    wholesale_min_qty = :wholesale_min_qty, sale_price = :sale_price,
                    min_order = :min_order, stock_qty = :stock_qty, image = :image,
                    is_featured = :is_featured, is_active = :is_active
             WHERE id = :id',
            $params
        );
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM products WHERE id = ?', [$id]);
    }

    public static function decrementStock(int $id, int $qty): void
    {
        Db::run('UPDATE products SET stock_qty = GREATEST(stock_qty - ?, 0) WHERE id = ?', [$qty, $id]);
    }

    public static function lowStock(int $threshold = 50, int $limit = 8): array
    {
        return Db::all(
            'SELECT id, name, unit, stock_qty FROM products
             WHERE is_active = 1 AND stock_qty <= ? ORDER BY stock_qty ASC LIMIT ' . (int) $limit,
            [$threshold]
        );
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i    = 1;
        while (true) {
            $sql    = 'SELECT id FROM products WHERE slug = ?' . ($ignoreId ? ' AND id <> ?' : '');
            $params = $ignoreId ? [$slug, $ignoreId] : [$slug];
            if (!Db::first($sql, $params)) {
                return $slug;
            }
            $slug = $base . '-' . (++$i);
        }
    }

    private static function bind(array $d): array
    {
        $optional = fn($value) => ($value === '' || $value === null) ? null : (float) $value;

        return [
            'category_id'       => $d['category_id'] ? (int) $d['category_id'] : null,
            'name'              => $d['name'],
            'slug'              => $d['slug'],
            'sku'               => $d['sku'] ?: null,
            'summary'           => $d['summary'] ?: null,
            'description'       => $d['description'] ?: null,
            'origin'            => $d['origin'] ?: null,
            'unit'              => $d['unit'] ?: 'unit',
            'retail_price'      => (float) $d['retail_price'],
            'wholesale_price'   => $optional($d['wholesale_price'] ?? null),
            'wholesale_min_qty' => max(1, (int) ($d['wholesale_min_qty'] ?? 10)),
            'sale_price'        => $optional($d['sale_price'] ?? null),
            'min_order'         => max(1, (int) ($d['min_order'] ?? 1)),
            'stock_qty'         => max(0, (int) ($d['stock_qty'] ?? 0)),
            'image'             => $d['image'] ?: null,
            'is_featured'       => (int) (bool) ($d['is_featured'] ?? 0),
            'is_active'         => (int) (bool) ($d['is_active'] ?? 1),
        ];
    }
}
