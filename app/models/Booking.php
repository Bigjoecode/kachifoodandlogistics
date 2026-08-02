<?php
/**
 * Logistics bookings — truck and van hire, relocations and interstate runs.
 * Entirely independent of the food catalogue: a customer can book transport
 * without buying anything.
 */
class Booking
{
    public static function serviceTypes(): array
    {
        return [
            'Pickup', 'Dropoff', 'Truck Hire', 'Van Hire', 'Office Relocation',
            'Warehouse Delivery', 'Market Delivery', 'Food Delivery',
            'Business Delivery', 'Interstate Haulage',
        ];
    }

    /** Vehicle => [base fare, payload description, capacity in kg]. */
    public static function vehicleTypes(): array
    {
        return [
            'Motorcycle'   => [4000.00,   'Documents and small parcels',      50],
            'Mini Van'     => [14000.00,  'Shopping loads and small moves',   500],
            'Cargo Van'    => [22000.00,  'Pallet loads and market runs',     1200],
            'Pickup Truck' => [30000.00,  'Building materials and bulk bags', 2000],
            'Mini Truck'   => [55000.00,  'Shop restocking and relocations',  5000],
            'Large Truck'  => [110000.00, 'Full loads and interstate runs',   15000],
            'Flatbed'      => [145000.00, 'Machinery and oversize cargo',     20000],
        ];
    }

    /** Distance band => multiplier applied to the vehicle base fare. */
    public static function distanceBands(): array
    {
        return [
            'Within Asaba'              => 1.00,
            'Within Delta State'        => 1.65,
            'Neighbouring state'        => 2.60,
            'Interstate (long haul)'    => 4.20,
        ];
    }

    public static function urgencyLevels(): array
    {
        return [
            'Standard (24 - 72 hours)' => 1.00,
            'Same day'                 => 1.35,
            'Express (within 3 hours)' => 1.75,
        ];
    }

    public static function pickupTimes(): array
    {
        return ['7:00am - 10:00am', '10:00am - 1:00pm', '1:00pm - 4:00pm', '4:00pm - 7:00pm', 'Flexible'];
    }

    public const LABOUR_FEE = 9000.00;

    /**
     * Quote a booking up front. Returns the total plus the breakdown, so the
     * same numbers can be shown to the customer and stored for the ops desk.
     */
    public static function estimate(string $vehicle, string $band, int $weightKg, string $urgency, bool $labour): array
    {
        $vehicles = self::vehicleTypes();
        $base     = $vehicles[$vehicle][0] ?? 0.0;
        $capacity = $vehicles[$vehicle][2] ?? 1;

        $distanceMultiplier = self::distanceBands()[$band] ?? 1.0;
        $urgencyMultiplier  = self::urgencyLevels()[$urgency] ?? 1.0;

        // Loads beyond the vehicle's comfortable payload attract a surcharge
        // rather than being silently accepted at the base fare.
        $overload   = max(0, $weightKg - $capacity);
        $overloadPc = $capacity > 0 ? min(0.5, ($overload / $capacity) * 0.35) : 0.0;

        $distanceCost = $base * ($distanceMultiplier - 1);
        $weightCost   = $base * $overloadPc;
        $subtotal     = $base + $distanceCost + $weightCost;
        $urgencyCost  = $subtotal * ($urgencyMultiplier - 1);
        $labourCost   = $labour ? self::LABOUR_FEE : 0.0;

        return [
            'base'     => round($base, 2),
            'distance' => round($distanceCost, 2),
            'weight'   => round($weightCost, 2),
            'urgency'  => round($urgencyCost, 2),
            'labour'   => round($labourCost, 2),
            'total'    => round($subtotal + $urgencyCost + $labourCost, 2),
        ];
    }

    public static function nextReference(): string
    {
        do {
            $reference = 'KFL-L-' . date('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Db::first('SELECT id FROM logistics_bookings WHERE reference = ?', [$reference]));

        return $reference;
    }

    public static function create(array $data): array
    {
        $estimate = self::estimate(
            $data['vehicle_type'],
            $data['distance_band'],
            (int) $data['weight_kg'],
            $data['urgency'],
            (bool) $data['needs_labour']
        );

        $reference = self::nextReference();

        $bookingId = Db::insert(
            'INSERT INTO logistics_bookings
                (reference, user_id, status, customer_name, email, phone, company, service_type, vehicle_type,
                 pickup_address, pickup_city, destination_address, destination_city, pickup_date, pickup_time,
                 distance_band, weight_kg, urgency, needs_labour, description, instructions, estimated_price)
             VALUES
                (:reference, :user_id, :status, :customer_name, :email, :phone, :company, :service_type, :vehicle_type,
                 :pickup_address, :pickup_city, :destination_address, :destination_city, :pickup_date, :pickup_time,
                 :distance_band, :weight_kg, :urgency, :needs_labour, :description, :instructions, :estimated_price)',
            [
                'reference'           => $reference,
                'user_id'             => $data['user_id'] ?: null,
                'status'              => 'pending',
                'customer_name'       => $data['customer_name'],
                'email'               => $data['email'],
                'phone'               => $data['phone'],
                'company'             => $data['company'] ?: null,
                'service_type'        => $data['service_type'],
                'vehicle_type'        => $data['vehicle_type'],
                'pickup_address'      => $data['pickup_address'],
                'pickup_city'         => $data['pickup_city'],
                'destination_address' => $data['destination_address'],
                'destination_city'    => $data['destination_city'],
                'pickup_date'         => $data['pickup_date'] ?: null,
                'pickup_time'         => $data['pickup_time'] ?: null,
                'distance_band'       => $data['distance_band'],
                'weight_kg'           => max(0, (int) $data['weight_kg']),
                'urgency'             => $data['urgency'],
                'needs_labour'        => (int) (bool) $data['needs_labour'],
                'description'         => $data['description'] ?: null,
                'instructions'        => $data['instructions'] ?: null,
                'estimated_price'     => $estimate['total'],
            ]
        );

        self::addEvent($bookingId, 'pending', 'Booking request received. Our dispatch desk will confirm the vehicle and firm up pricing.', $data['pickup_city'], 'System');

        return self::find($bookingId);
    }

    public static function find(int $id): ?array
    {
        return Db::first('SELECT * FROM logistics_bookings WHERE id = ?', [$id]);
    }

    public static function findByReference(string $reference): ?array
    {
        return Db::first('SELECT * FROM logistics_bookings WHERE reference = ?', [strtoupper(trim($reference))]);
    }

    /** Same rule as orders: the reference alone is not enough to look one up. */
    public static function track(string $reference, string $contact): ?array
    {
        $booking = self::findByReference($reference);
        if (!$booking) {
            return null;
        }
        $contact = strtolower(trim($contact));
        $digits  = preg_replace('/\D+/', '', $contact);
        $phone   = preg_replace('/\D+/', '', $booking['phone']);

        $emailMatch = $contact !== '' && strtolower($booking['email']) === $contact;
        $phoneMatch = $digits !== '' && $phone !== '' && str_ends_with($phone, substr($digits, -9));

        return ($emailMatch || $phoneMatch) ? $booking : null;
    }

    public static function events(int $bookingId): array
    {
        return Db::all('SELECT * FROM logistics_events WHERE booking_id = ? ORDER BY created_at, id', [$bookingId]);
    }

    public static function addEvent(int $bookingId, string $status, ?string $note = null, ?string $location = null, ?string $by = null): void
    {
        Db::run(
            'INSERT INTO logistics_events (booking_id, status, note, location, created_by) VALUES (?, ?, ?, ?, ?)',
            [$bookingId, $status, $note ?: null, $location ?: null, $by ?: null]
        );
    }

    public static function updateStatus(int $bookingId, string $status, ?string $note = null, ?string $location = null, ?string $by = null): void
    {
        Db::run('UPDATE logistics_bookings SET status = ? WHERE id = ?', [$status, $bookingId]);
        self::addEvent($bookingId, $status, $note, $location, $by);
    }

    public static function setQuote(int $bookingId, float $price): void
    {
        Db::run('UPDATE logistics_bookings SET quoted_price = ? WHERE id = ?', [$price, $bookingId]);
    }

    public static function assignDriver(int $bookingId, string $driver, string $vehicleReg): void
    {
        Db::run(
            'UPDATE logistics_bookings SET driver_name = ?, vehicle_reg = ? WHERE id = ?',
            [$driver ?: null, $vehicleReg ?: null, $bookingId]
        );
    }

    public static function statuses(): array
    {
        return [
            'pending'    => 'Pending',
            'quoted'     => 'Quoted',
            'confirmed'  => 'Confirmed',
            'assigned'   => 'Driver assigned',
            'in_transit' => 'In transit',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
        ];
    }

    /** Ordered milestones for the public tracking strip. */
    public static function milestones(): array
    {
        return ['confirmed', 'assigned', 'in_transit', 'completed'];
    }

    public static function forUser(int $userId, int $limit = 50): array
    {
        return Db::all(
            'SELECT * FROM logistics_bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function paginate(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]          = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['vehicle_type'])) {
            $where[]                = 'vehicle_type = :vehicle_type';
            $params['vehicle_type'] = $filters['vehicle_type'];
        }
        if (!empty($filters['q'])) {
            $where[]     = '(reference LIKE :q OR customer_name LIKE :q OR email LIKE :q OR phone LIKE :q
                             OR pickup_city LIKE :q OR destination_city LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $total   = (int) Db::value("SELECT COUNT(*) FROM logistics_bookings {$whereSql}", $params);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min($page, $pages);
        $offset  = ($page - 1) * $perPage;

        $rows = Db::all(
            "SELECT * FROM logistics_bookings {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    public static function stats(): array
    {
        return [
            'today'      => (int) Db::value('SELECT COUNT(*) FROM logistics_bookings WHERE DATE(created_at) = CURDATE()'),
            'open'       => (int) Db::value("SELECT COUNT(*) FROM logistics_bookings WHERE status IN ('pending','quoted','confirmed','assigned','in_transit')"),
            'awaiting'   => (int) Db::value("SELECT COUNT(*) FROM logistics_bookings WHERE status = 'pending'"),
            'completed'  => (int) Db::value("SELECT COUNT(*) FROM logistics_bookings WHERE status = 'completed'"),
            'value_month'=> (float) Db::value("SELECT COALESCE(SUM(COALESCE(quoted_price, estimated_price)),0) FROM logistics_bookings
                                               WHERE status NOT IN ('cancelled')
                                                 AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"),
        ];
    }

    public static function recent(int $limit = 5): array
    {
        return Db::all('SELECT * FROM logistics_bookings ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM logistics_bookings WHERE id = ?', [$id]);
    }
}
