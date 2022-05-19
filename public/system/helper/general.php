<?php
function token($length = 32)
{
    // Create random token
    $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $max = strlen($string) - 1;

    $token = '';

    for ($i = 0; $i < $length; $i++) {
        $token .= $string[mt_rand(0, $max)];
    }

    return $token;
}

/**
 * Backwards support for timing safe hash string comparisons
 *
 * http://php.net/manual/en/function.hash-equals.php
 */

if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        $known_string = (string)$known_string;
        $user_string = (string)$user_string;

        if (strlen($known_string) != strlen($user_string)) {
            return false;
        } else {
            $res = $known_string ^ $user_string;
            $ret = 0;

            for ($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);

            return !$ret;
        }
    }
}

function clearPhoneNumber($telephone)
{
    $symbols = array("(", ")", "-", " ");
    return str_replace($symbols, "", $telephone);
}

function nowMySQLTimestamp()
{
    return date('Y-m-d H:i:s');
}

function strToMySQLTimestamp(string $str)
{
    return date('Y-m-d H:i:s', strtotime($str));
}

function formatDatetime($format, $timestamp, $locale = '')
{
    $date_str = strftime($format, $timestamp);
    if (strpos($locale, '1251') !== false) {
        return iconv('cp1251', 'utf-8', $date_str);
    } else {
        return $date_str;
    }
}

function dateIntervalToSeconds(\DateInterval $diff)
{
    //Years to seconds
    $seconds = $diff->y * 12 * 30 * 24 * 60 * 60;
    //Monthes to seconds
    $seconds = $seconds + ($diff->m * 30 * 24 * 60 * 60);
    //Days to seconds
    $seconds = $seconds + ($diff->d * 24 * 60 * 60);
    //Hours to seconds
    $seconds = $seconds + (60 * 60 * $diff->h);
    //Minutes to seconds
    $seconds = $seconds + (60 * $diff->i);
    //Seconds
    $seconds = $seconds + $diff->s;

    return $seconds;
}

//TODO: Move somewhere ?!?!?!? Used for task tracker user permissions
function generatePermission(bool $read, $write, $create, $delete): string
{
    function g($bool)
    {
        return $bool ? '1' : '0';
    }

    return g($read) . g($write) . g($create) . g($delete);
}

function generatePermissionFromArray(array $data): string
{
    function g($bool)
    {
        return $bool ? '1' : '0';
    }

    return g(isset($data['read']) ? $data['read'] : 0) . g(isset($data['write']) ? $data['write'] : 0) . g(isset($data['create']) ? $data['create'] : 0) . g(isset($data['delete']) ? $data['delete'] : 0);
}

//----------------

function createTicketFromArray(\Registry &$registry, array $data): \Support\Ticket
{
    $obj = new \Support\Ticket($registry);
    $obj->mapData($data);
    return $obj;
}

function createProjectFromArray(\Registry &$registry, array $data): \Support\Project
{
    $obj = new \Support\Project($registry);
    $obj->mapData($data);
    return $obj;
}

function createUserFromArray(\Registry &$registry, array $data): \Support\User
{
    $obj = new \Support\User($registry);
    $obj->mapData($data);
    return $obj;
}

function createCommentFromArray(\Registry &$registry, array $data): \Support\Comment
{
    $obj = new \Support\Comment($registry);
    $obj->mapData($data);
    return $obj;
}

function createCategoryFromArray(\Registry &$registry, array $data): \Support\Category
{
    $obj = new \Support\Category($registry);
    $obj->mapData($data);
    return $obj;
}

//--------------------------------------

function getImageExtensions()
{
    return array('png', 'jpg', 'jpeg', 'gif', 'webp');
}

function getImageMimeTypes()
{
    return array(
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/gif',
        'image/webp'
    );
}

function extractDomain($domain)
{
    if (preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches)) {
        return $matches['domain'];
    } else {
        return $domain;
    }
}

function extractSubDomains($domain)
{
    $subdomains = $domain;
    $domain = extractDomain($subdomains);

    $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

    return $subdomains;
}

function isSubDomain($domain)
{
    return !empty(extractSubDomains($domain)) ? true : false;
}

function isLangSubDomain($domain)
{
    $available = array('ru', 'ua', 'en', 'uk');
    return in_array(strtolower(extractSubDomains($domain)), $available) ? true : false;
}

function removeSpaces($string)
{
    return str_replace(' ', '', $string);
}

function replaceSpaces($string, $replace = '-')
{
    return str_replace(' ', $replace, $string);
}

function removeSymbols($string)
{
    $symbols = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '+', '-', '=', ',', '.');
    return str_replace($symbols, '', $string);
}

//excel import functions
function getSeoUrlByName(string $name): string
{
    return replaceSpaces(utf8_strtolower(removeSymbols($name)));
}

function getFileExt($file)
{
    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
}

function transliterate($textcyr = null, $textlat = null)
{
    $cyr = array(
        'ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
        'Ж', 'Ч', 'Щ', 'Ш', 'Ю', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я', 'і', 'І', 'ї', 'Ї', 'є', 'Є');
    $lat = array(
        'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
        'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q', 'i', 'I', 'y', 'Y', 'e', 'E');
    if ($textcyr) return str_replace($cyr, $lat, $textcyr);
    else if ($textlat) return str_replace($lat, $cyr, $textlat);
    else return null;
}

function getUrlParamValue($url, $parameter)
{
    $components = parse_url(str_replace('&amp;', '&', $url));
    $query = array();
    parse_str($components['query'], $query);
    return $query[$parameter] ?? '';
}

function getUrlPathLast($url)
{
    $array = explode('/', $url);
    return end($array);
}

function getSeoUrlQueriesKeys()
{
    return array(
        'product_id',
        'manufacturer_id',
        'information_id',
        'category_id',
        'blog_category_id',
        'article_id'
    );
}

function extractRoute($uri)
{
    $route = '';
    if (($pos = utf8_strpos($uri, '?route=')) !== false) {
        $start = $pos + 7;
        if (($amp_pos = utf8_strpos($uri, '&')) !== false) {
            $length = $amp_pos - $start;
        } else {
            $length = utf8_strlen($uri) - $start;
        }
        $route = utf8_substr($uri, $start, $length);
    }
    return $route;
}

function renderPagination($page, $total, $limit, $url): string
{
    $pagination = new Pagination();
    $pagination->total = $total;
    $pagination->page = $page;
    $pagination->limit = $limit;
    $pagination->url = $url;

    return $pagination->render();
}

function isFrameworkDebug(): bool
{
    return defined('DEV') && constant('DEV');
}

function isCommandLineInterface(): bool
{
    return php_sapi_name() === 'cli';
}