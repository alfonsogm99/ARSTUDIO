<?php

$pass_plana1 = 'ARBARBER01';
$hash = password_hash($pass_plana1, PASSWORD_DEFAULT);

$pass_plana2 = 'admin';
$hash2 = password_hash($pass_plana2, PASSWORD_DEFAULT);


echo $hash;
echo "<br><br>";
echo $hash2;