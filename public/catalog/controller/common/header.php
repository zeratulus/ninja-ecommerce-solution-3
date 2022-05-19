<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCommonHeader extends \Ninja\Controller
{
    public function index()
    {
        //*** Ninja Studio - DebugBar Module ***/
        if (isFrameworkDebug()) {
            $data['dev_css_ver'] = '?v=' . mt_rand(0, 9999);
            //DebugBar configuration
            $debugBar = $this->getDebugBar();
            $renderer = $debugBar->getJavascriptRenderer();
            $renderer->setBasePath('/catalog/view/javascript/debugbar/');
            $renderer->setBaseUrl('/catalog/view/javascript/debugbar/');
            $renderer->disableVendor('jquery');
            $renderer->disableVendor('fontawesome');

            $data['debugbar'] = $renderer;
        }
        //*** Ninja Studio - DebugBar Module ***/

        // Analytics
        $data['analytics'] = $this->getLoader()->controller('common/cms/getAnalyticsScripts');

        if ($this->getRequest()->server['HTTPS']) {
            $server = $this->getConfig()->get('config_ssl');
        } else {
            $server = $this->getConfig()->get('config_url');
        }

        if (is_file(DIR_IMAGE . $this->getConfig()->get('config_icon'))) {
            $this->getDocument()->addLink($server . 'image/' . $this->getConfig()->get('config_icon'), 'icon');
        }
        $data['template_name'] = $this->getConfig()->get('config_theme');

        $data['title'] = $this->getDocument()->getTitle();

        $data['base'] = $server;
        $data['description'] = $this->getDocument()->getDescription();
        $data['keywords'] = $this->getDocument()->getKeywords();
        $data['links'] = $this->getDocument()->getLinks();
        $data['robots'] = $this->getDocument()->getRobots();
        $data['styles'] = $this->getDocument()->getStyles();
        $data['scripts'] = $this->getDocument()->getScripts('header');
        $data['lang'] = $this->getLanguage()->get('code');
        $data['direction'] = $this->getLanguage()->get('direction');

        $data['name'] = $this->getConfig()->get('config_name' . $this->language_id);

        if (is_file(DIR_IMAGE . $this->getConfig()->get('config_logo'))) {
            $data['logo'] = $server . 'image/' . $this->getConfig()->get('config_logo');
        } else {
            $data['logo'] = '';
        }

        $this->getLoader()->language('common/header');

        $host = isset($this->getRequest()->server['HTTPS']) && (($this->getRequest()->server['HTTPS'] == 'on') || ($this->getRequest()->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER;
        if ($this->getRequest()->server['REQUEST_URI'] == '/') {
            $data['og_url'] = $this->getUrl()->link('common/home');
        } else {
            $data['og_url'] = $host . substr($this->getRequest()->server['REQUEST_URI'], 1, (strlen($this->getRequest()->server['REQUEST_URI']) - 1));
        }

        $data['og_image'] = $this->getDocument()->getOgImage();

        // Wishlist
        if ($this->getCustomer()->isLogged()) {
            $this->getLoader()->model('account/wishlist');

            $data['text_wishlist'] = sprintf($this->getLanguage()->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
        } else {
            $data['text_wishlist'] = sprintf($this->getLanguage()->get('text_wishlist'), isset($this->getSession()->data['wishlist']) ? count($this->getSession()->data['wishlist']) : 0);
        }

        $data['text_logged'] = sprintf($this->getLanguage()->get('text_logged'), $this->getUrl()->link('account/account'), $this->getCustomer()->getFirstName(), $this->getUrl()->link('account/logout'));

        $data['home'] = $this->getUrl()->link('common/home');
        $data['wishlist'] = $this->getUrl()->link('account/wishlist', '', true);
        $data['logged'] = $this->getCustomer()->isLogged();
        $data['account'] = $this->getUrl()->link('account/account', '', true);
        $data['register'] = $this->getUrl()->link('account/register', '', true);
        $data['login'] = $this->getUrl()->link('account/login', '', true);
        $data['order'] = $this->getUrl()->link('account/order', '', true);
        $data['transaction'] = $this->getUrl()->link('account/transaction', '', true);
        $data['download'] = $this->getUrl()->link('account/download', '', true);
        $data['logout'] = $this->getUrl()->link('account/logout', '', true);
        $data['shopping_cart'] = $this->getUrl()->link('checkout/cart');
        $data['checkout'] = $this->getUrl()->link('checkout/checkout', '', true);

        //Telephones
        $data['telephones'] = $this->getLoader()->controller('common/cms/getTelephonesLinks');

        $data['language'] = $this->getLoader()->controller('common/language');
        $data['currency'] = $this->getLoader()->controller('common/currency');

        //Categories
        if (isset($this->getRequest()->get['path'])) {
            $parts = explode('_', (string)$this->getRequest()->get['path']);
        } else {
            $parts = array();
        }

        if (isset($parts[0])) {
            $data['category_id'] = $parts[0];
        } else {
            $data['category_id'] = 0;
        }

        if (isset($parts[1])) {
            $data['child_id'] = $parts[1];
        } else {
            $data['child_id'] = 0;
        }

        $this->getLoader()->model('catalog/category');

        $this->getLoader()->model('catalog/product');

        $data['categories'] = array();

        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            $children_data = array();

            if ($category['category_id'] == $data['category_id']) {
                $children = $this->model_catalog_category->getCategories($category['category_id']);

                foreach ($children as $child) {
                    $filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

                    $children_data[] = array(
                        'category_id' => $child['category_id'],
                        'name' => $child['name'] . ($this->getConfig()->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                        'href' => $this->getUrl()->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
                    );
                }
            }

            $filter_data = array(
                'filter_category_id' => $category['category_id'],
                'filter_sub_category' => true
            );

            $data['categories'][] = array(
                'category_id' => $category['category_id'],
                'name' => $category['name'] . ($this->getConfig()->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                'children' => $children_data,
                'href' => $this->getUrl()->link('product/category', 'path=' . $category['category_id'])
            );
        }

        $this->getLoader()->model('catalog/information');
        foreach ($this->model_catalog_information->getInformations() as $result) {
            if (!$result['bottom']) {
                $data['informations'][] = array(
                    'name' => $result['title'],
                    'href' => $this->getUrl()->link('information/information', 'information_id=' . $result['information_id'])
                );
            }
        }

        //Menu
        $data['contact'] = $this->getUrl()->link('information/contact');

        $data['service_menu']['return'] = array(
            'href' => $this->getUrl()->link('account/return/add', '', true),
            'name' => $this->getLanguage()->get('text_return')
        );
        $data['service_menu']['sitemap'] = array(
            'href' => $this->getUrl()->link('information/sitemap'),
            'name' => $this->getLanguage()->get('text_sitemap')
        );

        $data['service_menu']['tracking'] = array(
            'href' => $this->getUrl()->link('information/tracking'),
            'name' => $this->getLanguage()->get('text_tracking')
        );
        $data['service_menu']['manufacturer'] = array(
            'href' => $this->getUrl()->link('product/manufacturer'),
            'name' => $this->getLanguage()->get('text_manufacturer')
        );
        $data['service_menu']['voucher'] = array(
            'href' => $this->getUrl()->link('account/voucher', '', true),
            'name' => $this->getLanguage()->get('text_voucher')
        );
        $data['service_menu']['affiliate'] = array(
            'href' => $this->getUrl()->link('affiliate/login', '', true),
            'name' => $this->getLanguage()->get('text_affiliate')
        );
        $data['service_menu']['special'] = array(
            'href' => $this->getUrl()->link('product/special'),
            'name' => $this->getLanguage()->get('text_special')
        );

        if ($this->config->get('configblog_blog_menu')) {
            $data['blog_menu'] = $this->getLoader()->controller('blog/menu');
        } else {
            $data['blog_menu'] = '';
        }

        //*** Ninja Studio - Came From Module ***/
        $this->getLoader()->controller('customer/came_from');

        //$data['came_from_screen'] must be added to body as class
        if (isset($this->getSession()->data['came_from_processed_screen'])) {
            $data['came_from_screen'] = 'screen-processed';
        } else {
            $data['came_from_screen'] = 'screen-process';
        }
        $data['came_from_screen_link'] = $this->getUrl()->link('customer/came_from/screen', '', true);
        $data['session_id'] = $this->getSession()->getId();
        //*** Ninja Studio - Came From Module ***/

        $data['callback_request'] = $this->getLoader()->controller('tool/callback_request');

        $data['hreflang'] = $this->getLoader()->controller('common/cms/getLanguageAlternateLinks');

        $data['text_compare'] = sprintf($this->getLanguage()->get('text_compare'), (isset($this->getSession()->data['compare']) ? count($this->getSession()->data['compare']) : 0));
        $data['compare'] = $this->getUrl()->link('product/compare', '', true);
        $data['route'] = $this->getRequest()->getRoute();

        $data['search'] = $this->getLoader()->controller('common/search');
        $data['cart'] = $this->getLoader()->controller('common/cart');
        $data['menu'] = $this->getLoader()->controller('common/menu');

        return $this->getLoader()->view('common/header', $data);
    }

}