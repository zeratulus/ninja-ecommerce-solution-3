<?php

class ControllerPluginsCommentsComments extends \Ninja\NinjaController
{
    const ROUTE = 'plugins/comments/comments';
    const MODEL = 'model_plugins_comments_comments';

    public function isInstalled(): bool
    {
        $this->{self::MODEL}->isInstalled();
    }

    private $error = array();

    public function index()
    {
        $this->getLoader()->language(self::ROUTE);
        $data = $this->getLanguage()->all();

        $url = $this->processFilterUrl();
        $filter = $this->processFilter();

        if ($this->getRequest()->isRequestMethodPost() && $this->validate()) {
            $comment = new \Plugins\Comment($this->registry);
            $comment->mapData($this->getRequest()->post);
            $this->{self::MODEL}->addComment($comment);

            $this->getSession()->data['success'] = $this->getLanguage()->get('success');
        }

        $data['route'] = $this->getRequest()->getRoute();
        $form = new Ninja\FormBuilder\Form($this->registry, $this->getUrl()->link(self::ROUTE), Ninja\FormBuilder\Form::METHOD_POST);
        $form->addInput('text', 'author');
        $form->addInput('text', 'author');
        $form->addTextarea('text');

        $data['form_start'] = $form->renderFormStart();
        $data['form_body'] = $form->renderFormBody();
        $data['form_end'] = $form->renderFormEnd();

        if (isset($this->getSession()->data['success'])) {
            $data['success'] = $this->getSession()->data['success'];
            unset($this->getSession()->data['success']);
        }

        $data['errors'] = [];
        foreach ($this->error as $error) {
            $data['errors'][] = $error;
        }

        return $this->getLoader()->view(self::ROUTE, $data);
    }

    private function validate(): bool
    {
        if (empty(trim($this->getRequest()->issetPost('author')))) {
            $this->error['error_author'] = $this->getLanguage()->get('error_author');
        }
        if (empty(trim($this->getRequest()->issetPost('text')))) {
            $this->error['error_text'] = $this->getLanguage()->get('error_text');
        }
        if (empty(trim($this->getRequest()->issetPost('rating')))) {
            $this->error['error_rating'] = $this->getLanguage()->get('error_rating');
        }

        return empty($this->error);
    }

    private function processFilterUrl(): string
    {
        $url = '';
        if (!empty($filter_product = $this->getRequest()->issetGet('filter_product'))) {
            $url .= '&filter_product=' . urlencode(html_entity_decode($filter_product, ENT_QUOTES, 'UTF-8'));
        }

        if (!empty($filter_author = $this->getRequest()->issetGet('filter_author'))) {
            $url .= '&filter_author=' . urlencode(html_entity_decode($filter_author, ENT_QUOTES, 'UTF-8'));
        }

        if (!empty($filter_status = $this->getRequest()->issetGet('filter_status'))) {
            $url .= "&filter_status=$filter_status";
        }

        if (!empty($filter_date_added = $this->getRequest()->issetGet('filter_date_added'))) {
            $url .= "&filter_date_added=$filter_date_added";
        }

        if (!empty($sort = $this->getRequest()->get['sort'])) {
            $url .= "&sort=$sort";
        }

        if (!empty($order = $this->getRequest()->issetGet('order'))) {
            $url .= "&order=$order";
        }

        if (!empty($page = $this->getRequest()->issetGet('page'))) {
            $url .= "&page=$page";
        }

        return $url;
    }

    private function processFilter(): array
    {
        if (!empty($filter_route = $this->getRequest()->issetGet('filter_route'))) {
            $filter_route =  urlencode(html_entity_decode($filter_route, ENT_QUOTES, 'UTF-8'));
        }

        if (!empty($filter_author = $this->getRequest()->issetGet('filter_author'))) {
            $filter_author =  urlencode(html_entity_decode($filter_author, ENT_QUOTES, 'UTF-8'));
        }

        $filter_status = $this->getRequest()->issetGet('filter_status');
        $filter_date_added = $this->getRequest()->issetGet('filter_date_added');

        if (empty($sort = $this->getRequest()->get['sort'])) {
            $sort = 'r.date_added';
        }

        if (empty($order = $this->getRequest()->issetGet('order'))) {
            $order = 'DESC';
        }

        if (empty($page = $this->getRequest()->issetGet('page'))) {
            $page = 1;
        }

        $limit = $this->getConfig()->get('config_limit_admin');

        return [
            'filter_route' => $filter_route,
            'filter_author' => $filter_author,
            'filter_status' => $filter_status,
            'filter_date_added' => $filter_date_added,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        ];
    }

}