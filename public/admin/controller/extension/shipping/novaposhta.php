<?php

class ControllerExtensionShippingNovaposhta extends \Ninja\AdminController
{
    const ROUTE = 'extension/shipping/novaposhta';

    private $error = array();

    public function index()
    {
        $this->getLoader()->model(self::ROUTE);
        if (!$this->model_extension_shipping_novaposhta->isInstalled()) {
            $this->model_extension_shipping_novaposhta->install();
        }

        $this->getLoader()->language(self::ROUTE);
        $data = $this->getLanguage()->all();
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $this->getLoader()->model('setting/setting');

        if (($this->getRequest()->isRequestMethodPost()) && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_novaposhta', $this->getRequest()->post);

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken()));
        }

        $this->processWarningMessage($data);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken())
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_extensions'),
            'href' => $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken())
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken())
        );

        $data['action'] = $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken());

        $data['shipping_novaposhta_min_total_for_free_delivery'] = $this->getFromPostOrConfig('shipping_novaposhta_min_total_for_free_delivery');
        $data['shipping_novaposhta_delivery_order'] = $this->getFromPostOrConfig('shipping_novaposhta_delivery_order');
        $data['shipping_novaposhta_delivery_price'] = $this->getFromPostOrConfig('shipping_novaposhta_delivery_price');
        $data['shipping_novaposhta_delivery_insurance'] = $this->getFromPostOrConfig('shipping_novaposhta_delivery_insurance');
        $data['shipping_novaposhta_delivery_nal'] = $this->getFromPostOrConfig('shipping_novaposhta_delivery_nal');
        $data['shipping_novaposhta_geo_zone_id'] = $this->getFromPostOrConfig('shipping_novaposhta_geo_zone_id');
        $data['shipping_novaposhta_status'] = $this->getFromPostOrConfig('shipping_novaposhta_status');
        $data['shipping_novaposhta_sort_order'] = $this->getFromPostOrConfig('shipping_novaposhta_sort_order');

        $this->getLoader()->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->getDefaults($data);

        $this->getResponse()->setOutput($this->getLoader()->view(self::ROUTE, $data));
    }

    private function validate()
    {
        if (!$this->getUser()->hasPermission('modify', self::ROUTE)) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function update_offices()
    {

        $this->getLoader()->model(self::ROUTE);

        $key = $this->getConfig()->get('shipping_novaposhta_settings_api_key');
        if (!empty($key)) {
//            echo '<br><br><br><br><img src="../image/preloader.gif">';
//            echo '<h2 style="text-align: center;">Идёт обновление подождите!</h2>';
//            echo '<h1 style="text-align: center;" id="time">0</h1>';
//            echo '<script>var seconds = 0;
//            setInterval(function() {
//                seconds = seconds + 1;
//                document.querySelector("#time").innerText = seconds + "сек.";
//            }, 1000)</script>';

            $start = time();

            $api = new Delivery\NovaPoshtaApi2(
                $key,
                'ru', // Язык возвращаемых данных: ru (default) | ua | en
                false, // При ошибке в запросе выбрасывать Exception: FALSE (default) | TRUE
                'curl' // Используемый механизм запроса: curl (defalut) | file_get_content
            );

            $page_warehouses = 0;
            $warehouses_count = 0;
            $skipped = 0;
            $isSuccess = true;
            while ($isSuccess) {
                $warehouses = $api->getWarehouses('', $page_warehouses);
                $isSuccess = false;
                if (isset($warehouses['success'])) {
                    if ($warehouses['success']) {
                        if (count($warehouses['data']) > 0) {
                            foreach ($warehouses['data'] as $warehouse) {
                                if (!$this->model_extension_shipping_novaposhta->isWarehouseExists($warehouse['SiteKey'])) {
                                    $this->model_extension_shipping_novaposhta->addWarehouse($warehouse);
                                    $warehouses_count++;
                                } else {
                                    $skipped++;
                                }
                            }
                            $isSuccess = $warehouses['success'];
                            $page_warehouses++;
                        }
                    }
                }
            }

            $end = time();

            $execute_time = $end - $start;

            $info = array(
                'pages' => $page_warehouses,
                'count' => $warehouses_count,
                'skipped' => $skipped,
                'time' => $execute_time,
            );

            echo json_encode($info);
        } else {
            echo 'Error: NovaPoshta API 2.0 Key not set.';
        }
    }

    public function settings()
    {
        $this->getLoader()->language(self::ROUTE);
        $data = $this->getLanguage()->all();

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $this->getLoader()->model('setting/setting');

        if (($this->getRequest()->isRequestMethodPost()) && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_novaposhta_settings', $this->getRequest()->post);

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken()));
        }

        $this->processWarningMessage($data);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken()),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_extensions'),
            'href' => $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken()),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken()),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_settings'),
            'href' => $this->getUrl()->link(self::ROUTE . '/settings', 'user_token=' . $this->getUserToken()),
        );

        $data['action'] = $this->getUrl()->link(self::ROUTE . '/settings', 'user_token=' . $this->getUserToken());

        $data['shipping_novaposhta_settings_api_key'] = $this->getFromPostOrConfig('shipping_novaposhta_settings_api_key');

        $this->getDefaults($data);
        $this->getResponse()->setOutput($this->getLoader()->view(self::ROUTE . '/settings', $data));
    }

    public function warehouses_list()
    {
        $this->getLoader()->model(self::ROUTE);

        $url = '';

        if ($filter_name = $this->getRequest()->issetGet('filter_name')) {
            $url .= '&filter_name=' . $filter_name;
        }

        if ($filter_city = $this->getRequest()->issetGet('filter_city')) {
            $url .= '&filter_city=' . $filter_city;
        }

        if ($filter_address = $this->getRequest()->issetGet('filter_address')) {
            $url .= '&filter_address=' . $filter_address;
        }

        if ($sort = $this->getRequest()->issetGet('sort')) {
            $url .= '&sort=' . $sort;
        }

        if (isset($this->getRequest()->get['order'])) {
            $order = $this->getRequest()->get['order'];
            $url .= '&order=' . $this->getRequest()->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->getRequest()->get['page'])) {
            $page = $this->getRequest()->get['page'];
//            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->getLoader()->language(self::ROUTE);
        $data = $this->getLanguage()->all();
        $this->processWarningMessage($data);

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken()),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_extensions'),
            'href' => $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getUserToken()),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken()),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_warehouses_list'),
            'href' => $this->getUrl()->link(self::ROUTE . '/warehouses_list', 'user_token=' . $this->getUserToken()),
        );

        $warehouses_filter = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->getConfig()->get('config_limit_admin'),
            'limit' => $this->getConfig()->get('config_limit_admin')
        );

        if (!empty($filter_name)) {
            $warehouses_filter['filter_name'] = $filter_name;
            $data['filter_name'] = $filter_name;
        }
        if (!empty($filter_city)) {
            $warehouses_filter['filter_city'] = $filter_city;
            $data['filter_city'] = $filter_city;
        }
        if (!empty($filter_address)) {
            $warehouses_filter['filter_address'] = $filter_address;
            $data['filter_address'] = $filter_address;
        }

        $data['warehouses'] = $this->model_extension_shipping_novaposhta->getWarehouses($warehouses_filter);
        $warehouses_total = $this->model_extension_shipping_novaposhta->getWarehousesTotal($warehouses_filter);

        $data['pagination'] = renderPagination(
            $page,
            $warehouses_total,
            $this->getConfig()->get('config_limit_admin'),
            $this->getUrl()->link(self::ROUTE . '/warehouses_list', 'user_token=' . $this->getUserToken() . $url . '&page={page}')
        );

        $data['results'] = sprintf($this->getLanguage()->get('text_pagination'),
            ($warehouses_total) ? (($page - 1) * $this->getConfig()->get('config_limit_admin')) + 1 : 0,
            ((($page - 1) * $this->getConfig()->get('config_limit_admin')) > ($warehouses_total - $this->getConfig()->get('config_limit_admin'))) ? $warehouses_total : ((($page - 1) * $this->getConfig()->get('config_limit_admin')) + $this->getConfig()->get('config_limit_admin')),
            $warehouses_total,
            ceil($warehouses_total / $this->config->get('config_limit_admin'))
        );

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['route'] = self::ROUTE . '/warehouses_list';

        $this->getDefaults($data);

        $this->getResponse()->setOutput($this->getLoader()->view(self::ROUTE . '/warehouses', $data));
    }

    public function isInstalled()
    {
        if ($this->getUser()->isLogged() && isset($this->getRequest()->get['user_token']) && $this->validate()) {
            if ($this->getUserToken() == $this->getRequest()->get['user_token']) {
                $this->getLoader()->model(self::ROUTE);
                if ($this->model_extension_shipping_novaposhta->isInstalled()) {
                    echo true;
                } else {
                    echo false;
                }
            }
        }
    }

    public function install()
    {
        if ($this->getUser()->isLogged() && isset($this->getRequest()->get['user_token']) && $this->validate()) {
            if ($this->getUserToken() == $this->getRequest()->get['user_token']) {
                $this->getLoader()->model(self::ROUTE);
                $this->model_extension_shipping_novaposhta->install();
            }
        }
    }

    public function clear_warehouses()
    {

        if ($this->getUser()->isLogged() && isset($this->getRequest()->get['user_token']) && $this->validate()) {
            if ($this->getUserToken() == $this->getRequest()->get['user_token']) {
                $this->getLoader()->model(self::ROUTE);
                $this->model_extension_shipping_novaposhta->clearWarehouses();
            }
        }

    }

    public function editWarehouseModal()
    {
        if ($this->getUser()->isLogged() && $siteKey = $this->getRequest()->issetPost('siteKey')) {

            $this->getLoader()->language(self::ROUTE);

            $data = array();

            $this->getLoader()->model(self::ROUTE);
            $data['warehouse_info'] = $this->model_extension_shipping_novaposhta->getWarehouse($siteKey);

            $this->getResponse()->setOutput($this->getLoader()->view(self::ROUTE . '/edit_warehouse_modal', $data));
        }
    }

    private function getDefaults(&$data)
    {
        $data['user_token'] = $this->getUserToken();
        $data['calc'] = $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken());
        $data['settings'] = $this->getUrl()->link(self::ROUTE . '/settings', 'user_token=' . $this->getUserToken());
        $data['import'] = $this->getUrl()->link(self::ROUTE . '/update_offices', 'user_token=' . $this->getUserToken());
        $data['warehouses_href'] = $this->getUrl()->link(self::ROUTE . '/warehouses_list', 'user_token=' . $this->getUserToken());
        $data['editWarehouseModal'] = $this->getUrl()->link(self::ROUTE . '/editWarehouseModal', 'user_token=' . $this->getUserToken());
        $data['cancel'] = $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken());

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');
    }

}