<?php

function check_db() {
  if (!extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "[ERROR] PDO or PDO_MySQL extension is not loaded!\n");
    return "ERROR";
  }


  $dbUrl = getenv('DATABASE_URL');
  fwrite(STDERR, "[DEBUG] Starting check_db()...\n");
  if ($dbUrl) {
    fwrite(STDERR, "[DEBUG] Using DATABASE_URL: $dbUrl\n");
    $urlParts = parse_url($dbUrl);
    $host = $urlParts['host'] ?? 'localhost';
    $user = $urlParts['user'] ?? '';
    $pass = $urlParts['pass'] ?? '';
    $name = ltrim($urlParts['path'] ?? '', '/');
    $port = $urlParts['port'] ?? '3306';
  } else {
    fwrite(STDERR, "[DEBUG] Using legacy DB env variables\n");
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');
    $name = getenv('DB_NAME');
    $port = getenv('DB_PORT') ?: '3306';
  }
  fwrite(STDERR, "[DEBUG] DB Params: host=$host, user=$user, db=$name, port=$port\n");

  // Cấu hình PDO với SSL - Phù hợp với Azure/AWS/Cloud DB
  // Bạn có thể truyền file CA qua biến môi trường nếu cần
  $driverOptions = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT => 5,
  ];

  // Nếu môi trường yêu cầu SSL
  if (getenv('DB_SSL') === 'true') {
    fwrite(STDERR, "[DEBUG] Using SSL for DB connection\n");
    $driverOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $driverOptions[PDO::MYSQL_ATTR_SSL_CAPATH] = '/does/not/matter';
  }

  try {
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    fwrite(STDERR, "[DEBUG] Connecting with DSN: $dsn\n");
    $pdo = new PDO($dsn, $user, $pass, $driverOptions);
    fwrite(STDERR, "[DEBUG] Connected to DB, checking for 'users' table...\n");
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
      fwrite(STDERR, "[DEBUG] Table 'users' exists.\n");
      return "EXISTS";
    } else {
      fwrite(STDERR, "[DEBUG] Table 'users' does not exist.\n");
      return "EMPTY";
    }
  } catch (PDOException $e) {
    fwrite(STDERR, "Database Connection Error: " . $e->getMessage() . PHP_EOL);
    return "ERROR";
  }
}

echo check_db();