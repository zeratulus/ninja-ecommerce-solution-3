<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 30.07.2021
 * Time: 21:30
 */

class CustomItem
{
    protected Registry $_registry;
    public \Ninja\NinjaController $controller;

    /**
     * Project constructor.
     * @param \Registry $registry
     */
    public function __construct(\Registry &$registry)
    {
        $this->_registry = $registry;
        $this->controller = new \Ninja\Controller($registry);
    }

    /**
     * * For using this method all protected fields names in child classes must started from _ (protected $_some_var)
     * This uses for auto fill required data from models arrays
     * $data is array with keys and data values
     * $map contains required keys in data array
     * @param array $data data array
     * @param array $map required keys with auto fill
     * @throws \Exception
     */
    protected function mapper(array $data, array $map)
    {
        foreach ($map as $key) {
            if (!key_exists($key, $data)) {
//                throw new \Exception(get_class($this) . " - Can`t map data! Required key not found: {$key}");
            } else {
                $this->{'_' . $key} = $data[$key];
            }
        }
    }

    protected function generateLink(string $route, string $args): string
    {
        $url = $this->getComponent('url');
        $session = $this->getComponent('session');
        return $url->link($route, "{$args}&user_token={$session->data['user_token']}", true);
    }

    /**
     * Return already created objects from registry
     * @param string $component_name
     * @return mixed object class or false
     */
    public function getComponent(string $component_name)
    {
        if ($this->_registry->has($component_name)) {
            return $this->_registry->get($component_name);
        } else {
            return false;
        }
    }

    public function getUserInfo(int $uid): namespace\User
    {
        $this->controller->load->model('user/user');
        $result = $this->controller->model_user_user->getUserById($uid);
        $user = new namespace\User($this->_registry);
        $user->mapData($result);

        return $user;
    }

    public function formatDateAsLongString(string $timestamp): string
    {
        $days = array(
            $this->controller->getLanguage()->get('text_sunday'),
            $this->controller->getLanguage()->get('text_monday'),
            $this->controller->getLanguage()->get('text_tuesday'),
            $this->controller->getLanguage()->get('text_wednesday'),
            $this->controller->getLanguage()->get('text_thursday'),
            $this->controller->getLanguage()->get('text_friday'),
            $this->controller->getLanguage()->get('text_saturday'),
        );

        $months = array(
            1 => $this->controller->getLanguage()->get('text_january'),
            2 => $this->controller->getLanguage()->get('text_february'),
            3 => $this->controller->getLanguage()->get('text_march'),
            4 => $this->controller->getLanguage()->get('text_april'),
            5 => $this->controller->getLanguage()->get('text_may'),
            6 => $this->controller->getLanguage()->get('text_june'),
            7 => $this->controller->getLanguage()->get('text_july'),
            8 => $this->controller->getLanguage()->get('text_august'),
            9 => $this->controller->getLanguage()->get('text_september'),
            10 => $this->controller->getLanguage()->get('text_october'),
            11 => $this->controller->getLanguage()->get('text_november'),
            12 => $this->controller->getLanguage()->get('text_december'),
        );

        $date = new \DateTime($timestamp);
        $dow = $date->format('w'); //Порядковый номер дня недели 0 sunday, 1 monday - 6 saturday
        $dow_name = $days[$dow];
        $day = $date->format('j'); //Порядковый номер дня без ведущего нуля
        $month_num = $date->format('n'); //Порядковый номер месяца без ведущего нуля
        $month_name = $months[$month_num];

        //склонение к дате...
        if ($this->controller->getLanguage()->get('code') == 'ru') {
            //fix Авгуся & Маря должно быть Августа и Марта
            if (($month_num != 8) && ($month_num != 3)) {
                $month_name = utf8_substr($month_name, 0, utf8_strlen($month_name) - 1) . 'я';
            } elseif (($month_num == 8) || ($month_num == 3)) {
                $month_name = $month_name . 'а';
            }
        } //TODO: склонение к дате code = 'ua'


        $year = $date->format('Y');

        return "{$dow_name}, {$day} {$month_name} {$year}";
    }

    public function formatDatetimeAsLongString(string $timestamp): string
    {
        $date_str = $this->formatDateAsLongString($timestamp);

        $date = new \DateTime($timestamp);
        $time = $date->format('H:i');

        return "{$date_str} {$time}";
    }

    public function formatAsJsDate(string $timestamp): string
    {
        return str_replace('-', '/', $timestamp);
    }

}