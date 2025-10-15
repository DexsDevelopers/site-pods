<?php
// CLI: php tools/hash_password.php "SuaSenhaForteAqui"
if (php_sapi_name() !== 'cli') { http_response_code(403); echo "Somente CLI"; exit; }
$pass = $argv[1] ?? '';
if ($pass === '') { fwrite(STDERR, "Uso: php tools/hash_password.php \"SUA_SENHA\"\n"); exit(1); }
$hash = password_hash($pass, PASSWORD_BCRYPT);
echo $hash . PHP_EOL;


