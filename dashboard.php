<?php
session_start();
//session_destroy();
if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
    echo "welcom to dahsboard";
} else {
    header('Location: auth.php');
    exit;
}