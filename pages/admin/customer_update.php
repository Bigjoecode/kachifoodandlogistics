<?php
/** @var array $params */
$customer = User::find((int) ($params['id'] ?? 0));

if (!$customer) {
    flash('error', 'That account no longer exists.');
    redirect('/admin/customers');
}

// An admin must not be able to lock themselves out of their own back office.
if ((int) $customer['id'] === auth_id()) {
    flash('error', 'You cannot change your own role or access from here.');
    redirect('/admin/customers');
}

switch ((string) input('action')) {
    case 'role':
        $role = (string) input('role');
        if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
            flash('error', 'That is not a valid role.');
            break;
        }
        User::setRole((int) $customer['id'], $role);
        flash('success', $customer['name'] . ' is now ' . ($role === 'admin' ? 'an' : 'a') . ' ' . $role . '.');
        break;

    case 'toggle':
        $active = !(int) $customer['is_active'];
        User::setActive((int) $customer['id'], $active);
        flash('success', $customer['name'] . ' has been ' . ($active ? 'enabled' : 'disabled') . '.');
        break;

    default:
        flash('error', 'Unknown action.');
}

redirect('/admin/customers' . (input('role') && input('action') !== 'role' ? '?role=' . urlencode((string) input('role')) : ''));
