<?php
class Order
{
    /** KFL-20260801-4831 */
    public static function nextReference(): string
    {
        do {
            $reference = 'KFL-' . date('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Db::first('SELECT id FROM orders WHERE reference = ?', [$reference]));

        return $reference;
    }

    /**
     * Persist an order plus its lines and opening timeline event in one transaction.
     * $lines: [['product' => row|null, 'product_name', 'unit', 'unit_price', 'quantity']]
     */
    public static function create(array $data, array $lines): array
    {
        $pdo = Db::conn();
        $pdo->beginTransaction();

        try {
            $reference = self::nextReference();
            $subtotal  = 0.0;
            foreach ($lines as $line) {
                $subtotal += (float) $line['unit_price'] * (int) $line['quantity'];
            }
            $fee   = (float) ($data['delivery_fee'] ?? 0);
            $total = $subtotal + $fee;

            $orderId = Db::insert(
                'INSERT INTO orders (reference, user_id, type, status, customer_name, email, phone, company,
                                     delivery_address, city, state, delivery_date, delivery_window,
                                     logistics_service, notes, subtotal, delivery_fee, total, payment_method)
                 VALUES (:reference, :user_id, :type, :status, :customer_name, :email, :phone, :company,
                         :delivery_address, :city, :state, :delivery_date, :delivery_window,
                         :logistics_service, :notes, :subtotal, :delivery_fee, :total, :payment_method)',
                [
                    'reference'         => $reference,
                    'user_id'           => $data['user_id'] ?: null,
                    'type'              => $data['type'] ?? 'order',
                    'status'            => 'pending',
                    'customer_name'     => $data['customer_name'],
                    'email'             => $data['email'],
                    'phone'             => $data['phone'],
                    'company'           => $data['company'] ?: null,
                    'delivery_address'  => $data['delivery_address'],
                    'city'              => $data['city'],
                    'state'             => $data['state'],
                    'delivery_date'     => $data['delivery_date'] ?: null,
                    'delivery_window'   => $data['delivery_window'] ?: null,
                    'logistics_service' => $data['logistics_service'] ?: null,
                    'notes'             => $data['notes'] ?: null,
                    'subtotal'          => $subtotal,
                    'delivery_fee'      => $fee,
                    'total'             => $total,
                    'payment_method'    => $data['payment_method'] ?? 'transfer',
                ]
            );

            foreach ($lines as $line) {
                $qty   = (int) $line['quantity'];
                $price = (float) $line['unit_price'];
                Db::run(
                    'INSERT INTO order_items (order_id, product_id, product_name, unit, unit_price, quantity, line_total)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [$orderId, $line['product_id'] ?? null, $line['product_name'], $line['unit'], $price, $qty, $price * $qty]
                );
                if (($data['type'] ?? 'order') === 'order' && !empty($line['product_id'])) {
                    Product::decrementStock((int) $line['product_id'], $qty);
                }
            }

            self::addEvent($orderId, 'pending', ($data['type'] ?? 'order') === 'quote'
                ? 'Quote request received. Our team will price it and get back to you.'
                : 'Order received and awaiting confirmation.', 'Lagos', 'System');

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return self::find($orderId);
    }

    public static function find(int $id): ?array
    {
        return Db::first('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    public static function findByReference(string $reference): ?array
    {
        return Db::first('SELECT * FROM orders WHERE reference = ?', [strtoupper(trim($reference))]);
    }

    /**
     * Public tracking lookup: the reference alone is not enough, the caller must
     * also know the email or phone on the order.
     */
    public static function track(string $reference, string $contact): ?array
    {
        $order = self::findByReference($reference);
        if (!$order) {
            return null;
        }
        $contact = strtolower(trim($contact));
        $digits  = preg_replace('/\D+/', '', $contact);
        $phone   = preg_replace('/\D+/', '', $order['phone']);

        $emailMatch = $contact !== '' && strtolower($order['email']) === $contact;
        $phoneMatch = $digits !== '' && $phone !== '' && str_ends_with($phone, substr($digits, -9));

        return ($emailMatch || $phoneMatch) ? $order : null;
    }

    public static function items(int $orderId): array
    {
        return Db::all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    public static function events(int $orderId): array
    {
        return Db::all('SELECT * FROM order_events WHERE order_id = ? ORDER BY created_at, id', [$orderId]);
    }

    public static function addEvent(int $orderId, string $status, ?string $note = null, ?string $location = null, ?string $by = null): void
    {
        Db::run(
            'INSERT INTO order_events (order_id, status, note, location, created_by) VALUES (?, ?, ?, ?, ?)',
            [$orderId, $status, $note ?: null, $location ?: null, $by ?: null]
        );
    }

    public static function updateStatus(int $orderId, string $status, ?string $note = null, ?string $location = null, ?string $by = null): void
    {
        Db::run('UPDATE orders SET status = ? WHERE id = ?', [$status, $orderId]);
        self::addEvent($orderId, $status, $note, $location, $by);
    }

    public static function updatePayment(int $orderId, string $paymentStatus): void
    {
        Db::run('UPDATE orders SET payment_status = ? WHERE id = ?', [$paymentStatus, $orderId]);
    }

    /** Admin re-pricing of a quote: rewrite line prices, then recompute totals. */
    public static function repriceItems(int $orderId, array $prices, float $deliveryFee): void
    {
        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            foreach ($prices as $itemId => $price) {
                $price = (float) $price;
                Db::run(
                    'UPDATE order_items SET unit_price = ?, line_total = ? * quantity WHERE id = ? AND order_id = ?',
                    [$price, $price, (int) $itemId, $orderId]
                );
            }
            $subtotal = (float) Db::value('SELECT COALESCE(SUM(line_total), 0) FROM order_items WHERE order_id = ?', [$orderId]);
            Db::run(
                'UPDATE orders SET subtotal = ?, delivery_fee = ?, total = ? WHERE id = ?',
                [$subtotal, $deliveryFee, $subtotal + $deliveryFee, $orderId]
            );
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function forUser(int $userId, int $limit = 50): array
    {
        return Db::all(
            'SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count
             FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function paginate(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]          = 'o.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[]        = 'o.type = :type';
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['payment_status'])) {
            $where[]                  = 'o.payment_status = :payment_status';
            $params['payment_status'] = $filters['payment_status'];
        }
        if (!empty($filters['q'])) {
            $where[]     = '(o.reference LIKE :q OR o.customer_name LIKE :q OR o.email LIKE :q OR o.phone LIKE :q OR o.company LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['from'])) {
            $where[]        = 'o.created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[]      = 'o.created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $total   = (int) Db::value("SELECT COUNT(*) FROM orders o {$whereSql}", $params);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min($page, $pages);
        $offset  = ($page - 1) * $perPage;

        $rows = Db::all(
            "SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count
             FROM orders o {$whereSql} ORDER BY o.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    /** Headline numbers for the admin dashboard. */
    public static function stats(): array
    {
        $earned = "status NOT IN ('cancelled','pending','quoted')";

        return [
            'orders_today'   => (int) Db::value("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()"),
            'orders_total'   => (int) Db::value('SELECT COUNT(*) FROM orders'),
            'open_orders'    => (int) Db::value("SELECT COUNT(*) FROM orders WHERE status IN ('pending','confirmed','processing','dispatched','in_transit')"),
            'pending_quotes' => (int) Db::value("SELECT COUNT(*) FROM orders WHERE type = 'quote' AND status IN ('pending','quoted')"),
            'revenue_month'  => (float) Db::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE {$earned} AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"),
            'revenue_total'  => (float) Db::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE {$earned}"),
            'unpaid_value'   => (float) Db::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status <> 'paid' AND status NOT IN ('cancelled')"),
            'delivered'      => (int) Db::value("SELECT COUNT(*) FROM orders WHERE status = 'delivered'"),
        ];
    }

    /** Last N days of order counts and value, for the dashboard sparkline. */
    public static function dailyTotals(int $days = 14): array
    {
        $rows = Db::all(
            "SELECT DATE(created_at) AS day, COUNT(*) AS orders, COALESCE(SUM(total),0) AS value
             FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)",
            [$days]
        );
        $byDay = array_column($rows, null, 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day      = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = [
                'day'    => $day,
                'orders' => (int) ($byDay[$day]['orders'] ?? 0),
                'value'  => (float) ($byDay[$day]['value'] ?? 0),
            ];
        }
        return $series;
    }

    public static function statusBreakdown(): array
    {
        $rows  = Db::all('SELECT status, COUNT(*) AS total FROM orders GROUP BY status');
        $counts = array_fill_keys(array_keys(order_statuses()), 0);
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public static function recent(int $limit = 6): array
    {
        return Db::all('SELECT * FROM orders ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }

    /** Best sellers by quantity moved. */
    public static function topProducts(int $limit = 5): array
    {
        return Db::all(
            "SELECT i.product_name, SUM(i.quantity) AS qty, SUM(i.line_total) AS value
             FROM order_items i JOIN orders o ON o.id = i.order_id
             WHERE o.status NOT IN ('cancelled')
             GROUP BY i.product_name ORDER BY qty DESC LIMIT " . (int) $limit
        );
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM orders WHERE id = ?', [$id]);
    }
}
