<?php
$result = Message::paginate([
    'status' => (string) input('status', ''),
    'q'      => (string) input('q', ''),
    'page'   => input_int('page', 1),
]);
$unread = Message::unreadCount();

partial('admin_header', [
    'title'    => 'Messages',
    'subtitle' => $unread ? $unread . ' unread of ' . number_format($result['total']) . ' shown' : 'Everything has been read',
]);
?>

<nav class="tabs">
    <a href="<?= url('/admin/messages') ?>" class="<?= input('status', '') === '' ? 'is-active' : '' ?>">All</a>
    <a href="<?= url('/admin/messages') ?>?status=unread" class="<?= input('status') === 'unread' ? 'is-active' : '' ?>">
        Unread <?php if ($unread): ?>(<?= $unread ?>)<?php endif; ?>
    </a>
    <a href="<?= url('/admin/messages') ?>?status=read" class="<?= input('status') === 'read' ? 'is-active' : '' ?>">Read</a>
</nav>

<?php if (!$result['rows']): ?>
    <div class="empty">
        <div class="mark">0</div>
        <h3>No messages here</h3>
        <p>Enquiries sent through the contact form land in this inbox.</p>
    </div>
<?php else: ?>
    <div class="grid" style="gap:1rem">
        <?php foreach ($result['rows'] as $message): ?>
            <div class="card card-pad" style="<?= (int) $message['is_read'] ? '' : 'border-left:3px solid var(--accent-500)' ?>">
                <div class="flex-between flex-wrap mb-2">
                    <div>
                        <div class="flex-center gap-sm">
                            <span class="cell-title"><?= e($message['name']) ?></span>
                            <?php if (!(int) $message['is_read']): ?><span class="badge badge-accent">New</span><?php endif; ?>
                            <?php if ($message['subject']): ?><span class="badge badge-muted"><?= e($message['subject']) ?></span><?php endif; ?>
                        </div>
                        <div class="cell-sub">
                            <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a>
                            <?php if ($message['phone']): ?> &middot; <a href="tel:<?= e($message['phone']) ?>"><?= e($message['phone']) ?></a><?php endif; ?>
                            &middot; <?= e(date_human($message['created_at'], true)) ?>
                        </div>
                    </div>

                    <form method="post" action="<?= url('/admin/messages/' . $message['id']) ?>" class="flex gap-sm">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost btn-sm" type="submit" name="action" value="<?= (int) $message['is_read'] ? 'unread' : 'read' ?>">
                            Mark <?= (int) $message['is_read'] ? 'unread' : 'read' ?>
                        </button>
                        <a class="btn btn-ghost btn-sm" href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e($message['subject'] ?: 'Your enquiry') ?>">Reply</a>
                        <?php if (is_admin()): ?>
                            <button class="btn btn-ghost btn-sm" type="submit" name="action" value="delete"
                                    style="color:var(--danger-500)" formnovalidate>Delete</button>
                        <?php endif; ?>
                    </form>
                </div>

                <p class="small mb-0"><?= nl2br(e($message['message'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <?php partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php endif; ?>

<?php partial('admin_footer'); ?>
