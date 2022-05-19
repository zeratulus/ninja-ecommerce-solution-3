<?php

class ControllerCommonHome extends \Ninja\NinjaController
{

    public function index()
    {
        $this->getDocument()->setTitle($this->config->get('config_meta_title' . $this->language_id));
        $this->getDocument()->setDescription($this->config->get('config_meta_description' . $this->language_id));
        $this->getDocument()->setKeywords($this->config->get('config_meta_keyword' . $this->language_id));

        if (isset($this->getRequest()->get['route'])) {
            $canonical = $this->getUrl()->link('common/home');
            if ($this->getConfig()->get('config_seo_pro') && !$this->getConfig()->get('config_seopro_addslash')) {
                $canonical = rtrim($canonical, '/');
            }
            $this->getDocument()->addLink($canonical, 'canonical');
        }

        $ogImage = 'og-home.jpg';
        if (is_file(DIR_IMAGE . '/' . $ogImage)) {
            if ($this->getRequest()->server['HTTPS']) {
                $server = $this->getConfig()->get('config_ssl');
            } else {
                $server = $this->getConfig()->get('config_url');
            }
            $image = $server . 'image/' . $ogImage;
            $this->getDocument()->setOgImage($image);
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/home', $data));
    }

}