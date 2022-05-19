<?php

class ControllerExtensionShippingFree extends \Ninja\AdminController
{
    const ROUTE = 'extension/shipping/free';

    private $error = array();

    public function index()
    {
        $data = [];

        $this->getLoader()->language(self::ROUTE);

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $this->getLoader()->model('setting/setting');

        if (($this->getRequest()->isRequestMethodPost()) && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_free', $this->getRequest()->post);

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken() . '&type=shipping'));
        }

        $this->processWarningMessage($data);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken())
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_extension'),
            'href' => $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken() . '&type=shipping')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link('extension/shipping/free', 'user_token=' . $this->getSession()->data['user_token'])
        );

        $data['action'] = $this->getUrl()->link('extension/shipping/free', 'user_token=' . $this->getUserToken());

        $data['cancel'] = $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken() . '&type=shipping');

        $data['shipping_free_total'] = $this->getFromPostOrConfig('shipping_free_total');
        $data['shipping_free_geo_zone_id'] = $this->getFromPostOrConfig('shipping_free_geo_zone_id');

        $this->getLoader()->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['shipping_free_status'] = $this->getFromPostOrConfig('shipping_free_status');
        $data['shipping_free_sort_order'] = $this->getFromPostOrConfig('shipping_free_sort_order');

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view('extension/shipping/free', $data));
    }

    protected function validate()
    {
        if (!$this->getUser()->hasPermission('modify', 'extension/shipping/free')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return !$this->error;
    }
}