<?php
$result = User::paginate([
    'q'    => (string) input('q', ''),
    'role' => (string) input('role', ''),
    'page' => input_int('page', 1),
]);

partial('admin_header', [
    'title'    => 'Customers',
    'subtitle' => number_format($result['total']) . ' account' . ($result['total'] === 1 ? '' : 's'),
]);
?>

<nav class="tabs">
    <a href="<?= url('/admin/customers') ?>" class="<?= input('role', '') === '' ? 'is-active' : '' ?>">Everyone</a>
    <a href="<?= url('/admin/customers') ?>?role=customer" class="<?= input('role') === 'customer' ? 'is-active' : '' ?>">Customers</a>
    <a href="<?= url('/admin/customers') ?>?role=staff" class="<?= input('role') === 'staff' ? 'is-active' : '' ?>">Staff</a>
    <a href="<?= url('/admin/customers') ?>?role=admin" class="<?= input('role') === 'admin' ? 'is-active' : '' ?>">Admins</a>
</nav>

<form class="data-filters" method="get">
    <?php if (input('role')): ?><input type="hidden" name="role" value="<?= e(input('role')) ?>"><?php endif; ?>
    <div class="field">
        <label for="q">Search</label>
        <input class="input" id="q" name="q" value="<?= e(input('q', '')) ?>" placeholder="Name, email, company, phone">
    </div>
    <div class="flex gap-sm">
        <button class="btn btn-primary" type="submit">Search</button>
        <a class="btn btn-ghost" href="<?= url('/admin/customers') ?>">Reset</a>
    </div>
</form>

<?php if (!$result['rows']): ?>
    <div class="empty">
        <div class="mark">0</div>
        <h3>No accounts match</h3>
        <p>Clear the search to see everyone.</p>
        <a class="btn btn-primary" href="<?= url('/admin/customers') ?>">Show all</a>
    </div>
<?php else: ?>
    <div class="card table-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Account</th><th>Contact</th><th>Location</th><th class="num">Orders</th>
                        <th class="num">Lifetime</th><th>Role</th><th class="tight">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($result['rows'] as $customer): ?>
                    <tr>
                        <td>
                            <div class="cell-title"><?= e($customer['name']) ?></div>
                            <div class="cell-sub">
                                <?= e($customer['company'] ?: 'Individual') ?> &middot; joined <?= e(date_human($customer['created_at'])) ?>
                            </div>
                        </td>
                        <td class="small">
                            <a href="mailto:<?= e($customer['email']) ?>"><?= e($customer['email']) ?></a><br>
                            <span class="muted"><?= e($customer['phone'] ?: '--') ?></span>
                        </td>
                        <td class="small"><?= e(trim(($customer['city'] ?? '') . ', ' . ($customer['state'] ?? ''), ', ') ?: '--') ?></td>
                        <td class="num"><?= (int) $customer['order_count'] ?></td>
                        <td class="num strong"><?= money_short($customer['lifetime_value']) ?></td>
                        <td>
                            <span class="badge badge-<?= $customer['role'] === 'admin' ? 'brand' : ($customer['role'] === 'staff' ? 'info' : 'muted') ?>">
                                <?= e(ucfirst($customer['role'])) ?>
                            </span>
                            <?php if (!(int) $customer['is_active']): ?>
                                <span class="badge badge-danger">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="tight">
                            <?php if (is_admin() && (int) $customer['id'] !== auth_id()): ?>
                                <form method="post" action="<?= url('/admin/customers/' . $customer['id']) ?>" class="flex gap-sm">
                                    <?= csrf_field() ?>
                                    <select class="select" name="role" style="width:auto;max-width:100%;padding:.35rem 2rem .35rem .6rem;font-size:.82rem">
                                        <?php foreach (['customer' => 'Customer', 'staff' => 'Staff', 'admin' => 'Admin'] as $role => $label): ?>
                                            <option value="<?= $role ?>" <?= $customer['role'] === $role ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-ghost btn-sm" type="submit" name="action" value="role">Save</button>
                                    <button class="btn btn-ghost btn-sm" type="submit" name="action" value="toggle"
                                            style="color:var(--<?= (int) $customer['is_active'] ? 'danger-500' : 'brand-600' ?>)">
                                        <?= (int) $customer['is_active'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                            <?php elseif ((int) $customer['id'] === auth_id()): ?>
                                <span class="badge badge-brand">You</span>
                            <?php else: ?>
                                <span class="tiny muted">Admin only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php endif; ?>

<?php partial('admin_footer'); ?>
