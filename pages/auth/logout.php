<?php
if (auth_check()) {
    auth_logout();
    flash('success', 'You have been signed out.');
}
redirect('/');
