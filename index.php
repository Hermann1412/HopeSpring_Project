<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['mybook_userid'])) {
    header("Location: login.php");
    die;
}

include('app/pages/feed/index.php');

