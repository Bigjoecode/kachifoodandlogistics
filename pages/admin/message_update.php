<?php
/** @var array $params */
$message = Message::find((int) ($params['id'] ?? 0));

if (!$message) {
    flash('error', 'That message no longer exists.');
    redirect('/admin/messages');
}

switch ((string) input('action')) {
    case 'read':
        Message::markRead((int) $message['id'], true);
        flash('success', 'Marked as read.');
        break;

    case 'unread':
        Message::markRead((int) $message['id'], false);
        flash('success', 'Marked as unread.');
        break;

    case 'delete':
        if (!is_admin()) {
            flash('error', 'Only administrators can delete messages.');
            break;
        }
        Message::delete((int) $message['id']);
        flash('success', 'Message from ' . $message['name'] . ' deleted.');
        break;

    default:
        flash('error', 'Unknown action.');
}

redirect('/admin/messages');
