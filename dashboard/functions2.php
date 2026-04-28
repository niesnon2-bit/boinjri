<?php
declare(strict_types=1);

if (!defined('DEBUG')) {
    define('DEBUG', false);
}

function debug_mode($mode = null) {
    if ($mode === null) {
        $mode = defined('DEBUG') && constant('DEBUG');
    }
  if($mode) {
    // Report all PHP errors
    error_reporting(E_ALL);
  }
  else {
    // Turn off all PHP error reporting
    error_reporting(0);
  }
}

?>