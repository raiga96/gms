<?php
// Generate a random AES-256 key (32 bytes)
$aesKey = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));

// Generate a random AES-128 IV (16 bytes)
$iv = bin2hex(mcrypt_create_iv(8, MCRYPT_DEV_URANDOM));

echo "AES Key: $aesKey\n";
echo "Initialization Vector (IV): $iv\n";
?>
