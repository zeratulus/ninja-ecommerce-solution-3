<?php
// HTTP
const HTTP_SERVER = 'http://localhost/';

// HTTPS
const HTTPS_SERVER = 'http://localhost/';

// DIR
const DIR_STORAGE = 'C:/Projects/monsters-store-3/storage/';
const DIR_ROOT = __DIR__;
const DIR_APPLICATION = DIR_ROOT . '/catalog/';
const DIR_SYSTEM = DIR_ROOT . '/system/';
const DIR_IMAGE = DIR_ROOT . '/image/';
const DIR_LANGUAGE = DIR_APPLICATION . 'language/';
const DIR_TEMPLATE = DIR_APPLICATION . 'view/theme/';
const DIR_CONFIG = DIR_SYSTEM . 'config/';
const DIR_CACHE = DIR_STORAGE . 'cache/';
const DIR_DOWNLOAD = DIR_STORAGE . 'download/';
const DIR_LOGS = DIR_STORAGE . 'logs/';
const DIR_MODIFICATION = DIR_STORAGE . 'modification/';
const DIR_SESSION = DIR_STORAGE . 'session/';
const DIR_UPLOAD = DIR_STORAGE . 'upload/';

// DB
const DB_DRIVER = 'mysqli';
const DB_HOSTNAME = '10.10.1.10';
const DB_USERNAME = 'root';
const DB_PASSWORD = 'pass';
const DB_DATABASE = 'store';
const DB_PORT = '3306';
const DB_PREFIX = 'oc_';

const TWIG_CACHE = false;
//Dev
const DEV = true;
if (defined(DEV) && constant(DEV)) {
    ini_set('display_errors', 1);
}
//define('DEBUG_SQL', true);
