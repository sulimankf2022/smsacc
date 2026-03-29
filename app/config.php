<?php
define('APP_NAME', 'SMS Finance');
define('APP_VERSION', '1.0.0');
// Set to '' if app is at domain root, or '/subdir' if hosted in a subdirectory
define('BASE_URL', '');
define('DB_PATH', __DIR__ . '/../database/smsacc.db');
define('SESSION_NAME', 'smsacc_session');

// Supported currencies
define('CURRENCIES', ['USD', 'EUR', 'ILS']);
define('BASE_CURRENCY', 'USD');
