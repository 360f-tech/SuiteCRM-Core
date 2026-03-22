<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('sugarEntry', true);

// --- Configuration ---
$baseDir = '/var/www/html/SuiteCRM/public/legacy';
chdir($baseDir);

// --- Bootstrap SuiteCRM ---
require_once $baseDir . '/include/entryPoint.php';
require_once $baseDir . '/include/utils/autoloader.php';
require_once $baseDir . '/modules/Administration/QuickRepairAndRebuild.php';

echo "🛠  Starting Quick Repair & Rebuild cache...\n";

try {
  $repair = new RepairAndClear();

  global $current_user;
  $current_user = BeanFactory::newBean('Users');
  $current_user->getSystemUser();

  // Optionally enable full repair:
  // $repair->repairAndClearAll(['clearAll'], ['All Modules'], true, false);
  // $repair->clearVardefs();
  $repair->rebuildExtensions();
  // $repair->clearCoreCache();

  echo "✅ Repair completed!\n";
} catch (Throwable $e) {
  fwrite(STDERR, "‼️ ERROR: " . $e->getMessage() . "\n");
  exit(1);
}