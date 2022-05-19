<?php

class Request
{
    public $get = array();
    public $post = array();
    public $cookie = array();
    public $files = array();
    public $server = array();

    public function __construct()
    {
        $this->get = $this->clean($_GET);
        $this->post = $this->clean($_POST);
        $this->request = $this->clean($_REQUEST);
        $this->cookie = $this->clean($_COOKIE);
        $this->files = $this->clean($_FILES);
        $this->server = $this->clean($_SERVER);
    }

    /**
     * Recursively clean html chars
     * @param mixed $data
     * @return mixed
     */
    public function clean($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);

                $data[$this->clean($key)] = $this->clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
        }

        return $data;
    }

    /**
     * @deprecated
     */
    public function isset_post(string $key)
    {
        if (!empty($this->post[$key])) {
            return $this->post[$key];
        } else {
            return '';
        }
    }

    /**
     * @deprecated
     */
    public function isset_get(string $key)
    {
        if (!empty($this->get[$key])) {
            return $this->get[$key];
        } else {
            return '';
        }
    }

    public function issetPost(string $key)
    {
        if (!empty($this->post[$key])) {
            return $this->post[$key];
        } else {
            return '';
        }
    }

    public function issetGet(string $key)
    {
        if (!empty($this->get[$key])) {
            return $this->get[$key];
        } else {
            return '';
        }
    }

    public function isRequestMethodPost(): bool
    {
        return utf8_strtoupper($this->server['REQUEST_METHOD']) == 'POST';
    }

    public function isRequestMethodGet(): bool
    {
        return utf8_strtoupper($this->server['REQUEST_METHOD']) == 'GET';
    }

    public function getRoute(): string
    {
        $route = $this->issetGet('route');
        if (empty($route)) {
            $route = $this->issetGet('_route_');
        }
        return $route;
    }

}