<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: text/plain; charset=utf-8');
echo "APP_ENV=" . APP_ENV . "\n";
echo "APP_URL=" . APP_URL . "\n";

try {
  $stmt = $pdo->query('SELECT NOW() as now');
  $row = $stmt->fetch();
  echo "Conexão OK. NOW(): " . $row['now'] . "\n";
  $ver = $pdo->query('SELECT VERSION() v')->fetchColumn();
  echo "MySQL VERSION: $ver\n";
} catch (Throwable $e) {
  echo "Falha ao executar teste: " . $e->getMessage() . "\n";
}


