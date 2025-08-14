<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';

$user = new User();
$result = $user->logout();

redirectTo('../prakiraan-cuaca.php');
?>