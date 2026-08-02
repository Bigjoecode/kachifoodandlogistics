<?php
/**
 * One-shot installer: creates the database, loads schema + demo catalogue,
 * and seeds an admin account. Destructive — it drops existing tables — so it
 * only runs on an explicit POST confirmation.
 *
 * Delete this file once the site is live.
 */
require __DIR__ . '/config/config.php';
require __DIR__ . '/app/models/Booking.php';   // Booking::estimate() is pure, no DB needed

$log    = [];
$failed = null;

/** Split a .sql file into statements, ignoring semicolons inside quotes/comments. */
function sql_statements(string $sql): array
{
    $statements = [];
    $current    = '';
    $inString   = false;
    $quote      = '';
    $len        = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
        $char = $sql[$i];

        if ($inString) {
            $current .= $char;
            if ($char === '\\' && $i + 1 < $len) {      // escaped char inside a string
                $current .= $sql[++$i];
            } elseif ($char === $quote) {
                $inString = false;
            }
            continue;
        }

        if ($char === "'" || $char === '"') {
            $inString = true;
            $quote    = $char;
            $current .= $char;
            continue;
        }

        // Line comment: skip to end of line.
        if (($char === '-' && substr($sql, $i, 3) === '-- ') || $char === '#') {
            while ($i < $len && $sql[$i] !== "\n") {
                $i++;
            }
            $current .= "\n";
            continue;
        }

        if ($char === ';') {
            $statements[] = trim($current);
            $current      = '';
            continue;
        }

        $current .= $char;
    }

    if (trim($current) !== '') {
        $statements[] = trim($current);
    }

    return array_values(array_filter($statements, fn($s) => $s !== ''));
}

function run_sql_file(PDO $pdo, string $path, array &$log): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Cannot read {$path}");
    }
    $count = 0;
    foreach (sql_statements($sql) as $statement) {
        $pdo->exec($statement);
        $count++;
    }
    $log[] = basename($path) . " — {$count} statements executed";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . DB_NAME . '`');
        $log[] = 'Database `' . DB_NAME . '` ready';

        run_sql_file($pdo, ROOT_PATH . '/database/schema.sql', $log);
        run_sql_file($pdo, ROOT_PATH . '/database/seed.sql', $log);

        // --- Accounts -------------------------------------------------------
        $accounts = [
            ['KACHI Admin',   'admin@kachifoodandlogistics.com', '+2348031234567', 'admin123', 'admin',    'Kachi Foodstuff Supplies and Logistics Ltd'],
            ['Dispatch Desk', 'ops@kachifoodandlogistics.com',   '+2348031234568', 'ops12345', 'staff',    'Kachi Foodstuff Supplies and Logistics Ltd'],
            ['Amaka Obi',     'amaka@bellabites.ng',             '+2348090001111', 'demo1234', 'customer', 'Bella Bites Kitchen'],
        ];
        $insertUser = $pdo->prepare(
            'INSERT INTO users (name, email, phone, password_hash, role, company, address, city, state)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($accounts as [$name, $email, $phone, $password, $role, $company]) {
            $insertUser->execute([
                $name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role, $company,
                '18 Okpanam Road', 'Asaba', 'Delta',
            ]);
        }
        $log[] = count($accounts) . ' accounts created';

        // --- A couple of demo orders so the dashboard is not empty ----------
        $customerId = (int) $pdo->lastInsertId();

        $demoOrders = [
            [
                'ref' => 'KFL-' . date('Ymd', strtotime('-6 days')) . '-1041',
                'type' => 'order', 'status' => 'delivered',
                'items' => [[1, 'Local Rice 50kg', '50kg bag', 82500.00, 12], [23, 'Red Palm Oil 25L', '25L keg', 78000.00, 4]],
                'days' => -6,
                'events' => [
                    ['confirmed',  'Payment confirmed by finance.',             'Asaba'],
                    ['processing', 'Picked and packed at the Asaba warehouse.', 'Asaba'],
                    ['dispatched', 'Loaded on truck DEL-04.',                   'Asaba'],
                    ['delivered',  'Received by the store manager.',            'Asaba'],
                ],
            ],
            [
                'ref' => 'KFL-' . date('Ymd', strtotime('-1 day')) . '-1042',
                'type' => 'order', 'status' => 'in_transit',
                'items' => [[27, 'Frozen Whole Chicken 10kg', '10kg carton', 40500.00, 10]],
                'days' => -1,
                'events' => [
                    ['confirmed',  'Order confirmed.',                            'Asaba'],
                    ['dispatched', 'Reefer vehicle departed the depot at 06:40.', 'Asaba'],
                    ['in_transit', 'Passing Ughelli, ETA 90 minutes.',            'Ughelli'],
                ],
            ],
            [
                'ref' => 'KFL-' . date('Ymd') . '-1043',
                'type' => 'quote', 'status' => 'pending',
                'items' => [[5, 'Brown Honey Beans 50kg', '50kg bag', 111000.00, 20], [12, 'Spaghetti 500g (Carton of 20)', 'carton of 20', 12400.00, 30]],
                'days' => 0,
                'events' => [['pending', 'Quote request received, pricing in progress.', 'Asaba']],
            ],
        ];

        $insertOrder = $pdo->prepare(
            'INSERT INTO orders (reference, user_id, type, status, customer_name, email, phone, company,
                                 delivery_address, city, state, delivery_date, delivery_window, logistics_service,
                                 notes, subtotal, delivery_fee, total, payment_method, payment_status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertItem  = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, unit, unit_price, quantity, line_total)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insertEvent = $pdo->prepare(
            'INSERT INTO order_events (order_id, status, note, location, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($demoOrders as $o) {
            $subtotal = 0.0;
            foreach ($o['items'] as [$pid, $pname, $unit, $price, $qty]) {
                $subtotal += $price * $qty;
            }
            $fee   = $o['type'] === 'quote' ? 0.0 : ($subtotal >= FREE_DELIVERY_FROM ? 0.0 : DELIVERY_FEE);
            $when  = date('Y-m-d H:i:s', strtotime($o['days'] . ' days'));

            $insertOrder->execute([
                $o['ref'], $customerId, $o['type'], $o['status'],
                'Amaka Obi', 'amaka@bellabites.ng', '+2348090001111', 'Bella Bites Kitchen',
                '18 Okpanam Road', 'Asaba', 'Delta',
                date('Y-m-d', strtotime($o['days'] + 2 . ' days')), '8:00am - 12:00pm', 'Refrigerated delivery',
                'Call the store manager on arrival.',
                $subtotal, $fee, $subtotal + $fee, 'transfer',
                $o['status'] === 'delivered' ? 'paid' : 'unpaid', $when,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            foreach ($o['items'] as [$pid, $pname, $unit, $price, $qty]) {
                $insertItem->execute([$orderId, $pid, $pname, $unit, $price, $qty, $price * $qty]);
            }
            $step = 0;
            foreach ($o['events'] as [$status, $note, $location]) {
                $insertEvent->execute([
                    $orderId, $status, $note, $location, 'Ops Desk',
                    date('Y-m-d H:i:s', strtotime($when . ' +' . (++$step * 6) . ' hours')),
                ]);
            }
        }
        $log[] = count($demoOrders) . ' demo orders created';

        // --- Demo logistics bookings ---------------------------------------
        $demoBookings = [
            [
                'ref' => 'KFL-L-' . date('Ymd', strtotime('-2 days')) . '-2071',
                'status' => 'in_transit', 'service' => 'Truck Hire', 'vehicle' => 'Mini Truck',
                'from' => ['12 Nnebisi Road', 'Asaba'], 'to' => ['Effurun Roundabout', 'Warri'],
                'band' => 'Within Delta State', 'weight' => 3200, 'urgency' => 'Standard (24 - 72 hours)',
                'labour' => 1, 'days' => -2, 'quoted' => 128000.00,
                'driver' => 'Emeka Nwosu', 'reg' => 'DEL-482-XA',
                'events' => [
                    ['confirmed',  'Vehicle confirmed for the requested date.', 'Asaba'],
                    ['assigned',   'Emeka Nwosu assigned on DEL-482-XA.',       'Asaba'],
                    ['in_transit', 'Departed Asaba, ETA Warri in two hours.',   'Ughelli'],
                ],
            ],
            [
                'ref' => 'KFL-L-' . date('Ymd') . '-2072',
                'status' => 'pending', 'service' => 'Office Relocation', 'vehicle' => 'Large Truck',
                'from' => ['5 Okpanam Road', 'Asaba'], 'to' => ['Airport Road', 'Sapele'],
                'band' => 'Within Delta State', 'weight' => 8500, 'urgency' => 'Same day',
                'labour' => 1, 'days' => 0, 'quoted' => null,
                'driver' => null, 'reg' => null,
                'events' => [['pending', 'Booking request received, checking vehicle availability.', 'Asaba']],
            ],
        ];

        $insertBooking = $pdo->prepare(
            'INSERT INTO logistics_bookings
                (reference, user_id, status, customer_name, email, phone, company, service_type, vehicle_type,
                 pickup_address, pickup_city, destination_address, destination_city, pickup_date, pickup_time,
                 distance_band, weight_kg, urgency, needs_labour, description, instructions,
                 estimated_price, quoted_price, driver_name, vehicle_reg, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertLogEvent = $pdo->prepare(
            'INSERT INTO logistics_events (booking_id, status, note, location, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($demoBookings as $b) {
            $when = date('Y-m-d H:i:s', strtotime($b['days'] . ' days'));
            // Seed the same figure the live estimator would produce.
            $estimate = Booking::estimate($b['vehicle'], $b['band'], $b['weight'], $b['urgency'], (bool) $b['labour']);

            $insertBooking->execute([
                $b['ref'], $customerId, $b['status'],
                'Amaka Obi', 'amaka@bellabites.ng', '+2348090001111', 'Bella Bites Kitchen',
                $b['service'], $b['vehicle'],
                $b['from'][0], $b['from'][1], $b['to'][0], $b['to'][1],
                date('Y-m-d', strtotime(($b['days'] + 1) . ' days')), '7:00am - 10:00am',
                $b['band'], $b['weight'], $b['urgency'], $b['labour'],
                'Palletised dry goods.', 'Call on arrival at the gate.',
                $estimate['total'], $b['quoted'], $b['driver'], $b['reg'], $when,
            ]);
            $bookingId = (int) $pdo->lastInsertId();

            $step = 0;
            foreach ($b['events'] as [$status, $note, $location]) {
                $insertLogEvent->execute([
                    $bookingId, $status, $note, $location, 'Dispatch Desk',
                    date('Y-m-d H:i:s', strtotime($when . ' +' . (++$step * 5) . ' hours')),
                ]);
            }
        }
        $log[] = count($demoBookings) . ' demo logistics bookings created';
    } catch (Throwable $e) {
        $failed = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/app.css">
    <style>
        body { display: grid; place-items: center; min-height: 100vh; padding: 2rem 1rem; }
        .installer { width: min(640px, 100%); }
    </style>
</head>
<body>
<main class="installer">
    <div class="card" style="padding:2rem">
        <span class="badge badge-brand">Installer</span>
        <h1 style="margin:.75rem 0 .25rem">Set up <?= APP_NAME ?></h1>
        <p class="muted">Creates the <code><?= DB_NAME ?></code> database, loads the schema and demo catalogue,
            and seeds accounts.</p>

        <?php if ($failed): ?>
            <div class="alert alert-error"><strong>Install failed.</strong><br><?= htmlspecialchars($failed) ?></div>
        <?php elseif ($log): ?>
            <div class="alert alert-success"><strong>Installed successfully.</strong></div>
            <ul class="tick-list">
                <?php foreach ($log as $line): ?>
                    <li><?= htmlspecialchars($line) ?></li>
                <?php endforeach; ?>
            </ul>
            <table class="table" style="margin:1.25rem 0">
                <thead><tr><th>Role</th><th>Email</th><th>Password</th></tr></thead>
                <tbody>
                    <tr><td>Admin</td><td>admin@kachifoodandlogistics.com</td><td><code>admin123</code></td></tr>
                    <tr><td>Staff</td><td>ops@kachifoodandlogistics.com</td><td><code>ops12345</code></td></tr>
                    <tr><td>Customer</td><td>amaka@bellabites.ng</td><td><code>demo1234</code></td></tr>
                </tbody>
            </table>
            <p class="muted">Change these passwords and delete <code>install.php</code> before going live.</p>
            <a class="btn btn-primary" href="<?= BASE_PATH ?>/">Open the site</a>
            <a class="btn btn-ghost" href="<?= BASE_PATH ?>/admin">Go to admin</a>
        <?php else: ?>
            <div class="alert alert-warn">
                This <strong>drops and recreates</strong> every table in <code><?= DB_NAME ?></code>.
                Any existing data is lost.
            </div>
            <form method="post">
                <button class="btn btn-primary" type="submit">Run the installer</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
