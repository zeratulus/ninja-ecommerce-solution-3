<?php
// Registry
$registry = new Registry();

// DebugBar
if (isFrameworkDebug()) {
    $debugbar = new DebugBar\exStandardDebugBar();
    $debugbar->addCollector(new DebugBar\Bridge\MySQLiCollector([]));
    $debugbar->getTimeLine()->startMeasure('app_start', 'Application initialize -> framework.php');
    $registry->set('debugbar', $debugbar);
}

// Config
$config = new Config();
$config->load('default');
$config->load($application_config);
$registry->set('config', $config);

// Log
$log = new Log($config->get('error_filename'));
$registry->set('log', $log);

date_default_timezone_set($config->get('date_timezone'));

set_error_handler(function ($code, $message, $file, $line) use ($log, $config) {
    // error suppressed with @
    if (error_reporting() === 0) {
        return false;
    }

    switch ($code) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error = 'Notice';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error = 'Warning';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error = 'Fatal Error';
            break;
        default:
            $error = 'Unknown';
            break;
    }

    if ($config->get('error_display')) {
        echo '<b>' . $error . '</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . '</b>';
    }

    $msg = 'PHP ' . $error . ':  ' . $message . ' in ' . $file . ' on line ' . $line;
//    $debugbar->getMessages()->addMessage($msg);

    if ($config->get('error_log')) {
        $log->write($msg);
    }

    return true;
});

// Event
$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
    foreach ($config->get('action_event') as $key => $value) {
        foreach ($value as $priority => $action) {
            $event->register($key, new Action($action), $priority);
        }
    }
}

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Request
$registry->set('request', new Request());

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$response->setCompression((int)$config->get('config_compression'));
$registry->set('response', $response);

// Database
if ($config->get('db_autostart')) {
    $registry->set('db', new DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
}

// Session
$session = new Session($config->get('session_engine'), $registry);
$registry->set('session', $session);

if ($config->get('session_autostart')) {
    /*
    We are adding the session cookie outside of the session class as I believe
    PHP messed up in a big way handling sessions. Why in the hell is it so hard to
    have more than one concurrent session using cookies!

    Is it not better to have multiple cookies when accessing parts of the system
    that requires different cookie sessions for security reasons.

    Also cookies can be accessed via the URL parameters. So why force only one cookie
    for all sessions!
    */

    if (isset($_COOKIE[$config->get('session_name')])) {
        $session_id = $_COOKIE[$config->get('session_name')];
    } else {
        $session_id = '';
    }

    $session->start($session_id);

    setcookie($config->get('session_name'), $session->getId(), ini_get('session.cookie_lifetime'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
}

// Cache
$registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));

// Url
if ($config->get('url_autostart')) {
    $registry->set('url', new Url($config->get('site_url'), $config->get('site_ssl')));
}

// Language
$language = new Language($config->get('language_directory'));
$language->load($config->get('language_directory'));
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// BrowserDetection
$registry->set('browserdetection', new BrowserDetection());

// Config Autoload
if ($config->has('config_autoload')) {
    foreach ($config->get('config_autoload') as $value) {
        $loader->config($value);
    }
}

// Language Autoload
if ($config->has('language_autoload')) {
    foreach ($config->get('language_autoload') as $value) {
        $loader->language($value);
    }
}

// Library Autoload
if ($config->has('library_autoload')) {
    foreach ($config->get('library_autoload') as $value) {
        $loader->library($value);
    }
}

// Model Autoload
if ($config->has('model_autoload')) {
    foreach ($config->get('model_autoload') as $value) {
        $loader->model($value);
    }
}

// Route
$route = new Router($registry);

// Pre Actions
if ($config->has('action_pre_action')) {
    foreach ($config->get('action_pre_action') as $value) {
        $route->addPreAction(new Action($value));
    }
}

if (isFrameworkDebug()) {
    if ($config->has('app') && $config->get('app') === 'admin') {
        $debugbar->addCollector(new DebugBar\DataCollector\ConfigCollector($config->getSortedData()));

        //Settings from database
        $query = $registry->get('db')->query("SELECT * FROM " . DB_PREFIX . "setting ORDER BY code ASC");
        $settings = array();
        foreach ($query->rows as $item) {
            $settings[$item['setting_id']] = array(
                'code' => $item['code'],
                'key' => $item['key'],
                'value' => $item['value'],
                'serialized' => $item['serialized'],
                'store_id' => $item['store_id']
            );
        }

        $settingsCollector = new DebugBar\DataCollector\ConfigCollector($settings, 'settings');
        $debugbar->addCollector($settingsCollector);
    }

    $debugbar->getTimeLine()->stopMeasure('app_start');
}

// Dispatch
$route->dispatch(new Action($config->get('action_router')), new Action($config->get('action_error')));

// Output
$response->output();
