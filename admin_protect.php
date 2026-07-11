<?php
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard_admin.php');
}
?>
