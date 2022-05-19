<?php

use \Ninja\FormBuilder\Form;

class ControllerExtensionShippingNovaposhta extends \Ninja\NinjaController
{
    private string $route = 'extension/shipping/novaposhta';
    private string $model = 'extension_shipping_novaposhta';

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language_code = $this->getSession()->data['language'];
        if (utf8_strpos($this->language_code, 'ua') !== false) {
            $this->language_postfix = 'ua';
        } else {
            $this->language_postfix = 'ru';
        }
    }

    public function index()
    {
        $data = [];
        $this->getLoader()->language($this->route);
        $this->getLoader()->model($this->route);

        $filter = [];
        if (!empty($city = $this->getRequest()->issetPost('city'))) {
             $data['filter_city'] = $city;
             $filter['filter_city'] = $city;
        }

        $data['action'] = $this->getUrl()->link("{$this->route}/get_warehouses", '');
        $form = new Form($this->registry, '', );
        $cities = $this->{'model_'.$this->model}->getCities();
        $cities_options = ['' => $this->getLanguage()->get('text_select')];
        foreach ($cities as $city) {
            $cities_options[$city["city_{$this->language_postfix}"]] = $city["city_{$this->language_postfix}"];
        }
        $form->addSelect('city', $cities_options);

        $warehouses = [];
        if (!empty($filter)) {
            $warehouses = $this->{'model_'.$this->model}->getWarehouses($filter);
        }
        $warehouses_options = [];
        foreach ($warehouses as $warehouse) {
            $warehouses_options[''] = $warehouse[''];
        }
        $form->addSelect('warehouse', $warehouses_options);
        $data['form'] = $form->renderFormBody();
        if (utf8_strpos($this->language_code, '-') !== false) {
            $tmp = explode('-', $this->language_code);
            $tmp[1] = utf8_strtoupper($tmp[1]);
            $data['bootstrap_select_i18n'] = implode('_', $tmp);
        }

        $this->getResponse()->setOutput($this->getLoader()->view($this->route, $data));
    }

    public function get_warehouses()
    {
        $data = [];
        $this->getLoader()->language($this->route);
        $this->getLoader()->model($this->route);

        $filter = [];
        if (!empty($city = $this->getRequest()->issetPost('city'))) {
            $data['filter_city'] = $city;
            $filter['filter_city'] = $city;
        }
        if (!empty($address = $this->getRequest()->issetPost('address'))) {
            $data['filter_address'] = $address;
            $filter['filter_address'] = $address;
        }
        $warehouses = $this->{'model_'.$this->model}->getWarehouses($filter);
        $data['warehouses'] = [
            '' => $this->getLanguage()->get('text_select')
        ];
        foreach ($warehouses as $warehouse) {
            if ($this->language_postfix == 'ua') {
                $data['warehouses'][$warehouse['Description']] = $warehouse['Description'];
            } else {
                $data['warehouses'][$warehouse['DescriptionRu']] = $warehouse['DescriptionRu'];
            }
        }
        $data['success'] = true;

        $app = new \Tools\Monsters\ApplicationJson($this->registry);
        $app->setOutput($data);
    }

}