<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 17.07.19
 * Time: 22:02
 */

class Validator
{
    private Registry $_registry;
    private $_request;
    private $_user;

    public function __construct(Registry &$registry)
    {
        $this->_registry = $registry;

        $this->_request = $this->_registry->get('request');

        $this->_user = $this->_registry->get('user');
    }

    public function isEmpty($data)
    {
        return empty($data);
    }

    public function isBoolean($data)
    {
        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
    }

    public function isInteger($data)
    {
        return filter_var($data, FILTER_VALIDATE_INT);
    }

    public function isFloat($data)
    {
        return filter_var($data, FILTER_VALIDATE_FLOAT);
    }

    public function isEmail($data)
    {
        if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    public function isDomainName($data)
    {
        return filter_var($data, FILTER_VALIDATE_DOMAIN);
    }

    public function isIp($data)
    {
        return filter_var($data, FILTER_VALIDATE_IP);
    }

    public function isMac($data)
    {
        return filter_var($data, FILTER_VALIDATE_MAC);
    }

    public function isUrl($data)
    {
        return filter_var($data, FILTER_VALIDATE_URL);
    }

    public function isStringLengthBetween($data, $length_start, $length_end)
    {
        return (utf8_strlen($data) < $length_start) || (utf8_strlen($data) > $length_end);
    }

    public function isStringLengthLess($data, $length)
    {
        return (utf8_strlen($data) < $length);
    }

    public function isStringLengthMore($data, $length)
    {
        return (utf8_strlen($data) > $length);
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isRequestMethodPost()
    {
        return $this->_request->server['REQUEST_METHOD'] == 'POST';
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isRequestMethodGet()
    {
        return $this->_request->server['REQUEST_METHOD'] == 'GET';
    }

    /**
     * @deprecated
     * @param $key
     * @return bool
     */
    public function issetRequestPost($key)
    {
        return isset($this->_request->post[$key]);
    }

    /**
     * @deprecated
     * @param $key
     * @return bool
     */
    public function issetRequestGet($key)
    {
        return isset($this->_request->get[$key]);
    }

    /**
     * Check if user has permission
     * @param string $permission  example 'modify || access || '
     * @param string $module_route  example 'catalog/product'
     * @return mixed
     */
    public function hasPermission(string $permission, string $module_route)
    {
        return $this->_user->hasPermission($permission, $module_route);
    }

}
