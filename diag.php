<?php
require_once 'helpers/auth.php';
session_start();
echo "Username: " . ($_SESSION['user']['username'] ?? 'NULL') . "\n";
echo "Role: " . auth_get_role() . "\n";
echo "Is Panitia: " . (auth_is_panitia() ? 'YES' : 'NO') . "\n";
echo "Session Data: ";
print_r($_SESSION['user']);
