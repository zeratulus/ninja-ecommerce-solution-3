<?php
ini_set('display_errors', 1);
// HTTP
define('HTTP_SERVER', 'http://monsters-store-3.local/admin/');
define('HTTP_CATALOG', 'http://monsters-store-3.local/');

// HTTPS
define('HTTPS_SERVER', 'https://monsters-store-3.local/admin/');
define('HTTPS_CATALOG', 'https://monsters-store-3.local/');

// DIR
const DIR_ROOT = 'c:/Projects/monsters-store-3/public/';
const DIR_STORAGE = 'C:/Projects/monsters-store-3/storage/';
const DIR_APPLICATION = DIR_ROOT . 'admin/';
const DIR_SYSTEM = DIR_ROOT . 'system/';
const DIR_IMAGE = DIR_ROOT . 'image/';
const DIR_CATALOG = DIR_ROOT . 'catalog/';
const DIR_LANGUAGE = DIR_APPLICATION . 'language/';
const DIR_TEMPLATE = DIR_APPLICATION . 'view/template/';
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

// OpenCart API
const OPENCART_SERVER = 'https://www.opencart.com/';
const OPENCARTFORUM_SERVER = 'https://opencartforum.com/';

const DEV = true;