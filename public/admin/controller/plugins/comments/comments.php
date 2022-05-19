<?php

class ControllerPluginsCommentsComments extends \Ninja\AdminController
{
    const ROUTE = 'plugins/comments/comments';
    const MODEL = 'model_plugins_comments_comments';

    private $error = array();

    public function index()
    {
        $this->getLoader()->language(self::ROUTE);

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $this->getLoader()->model(self::ROUTE);
        if (!$this->{self::MODEL}->isInstalled()) {
            $this->{self::MODEL}->install();
        }

        $this->getList();
    }

    public function add()
    {
        $this->getLoader()->language(self::ROUTE);
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model(self::ROUTE);

        if ($this->getRequest()->isRequestMethodPost() && $this->validateForm()) {
            $comment = new \Plugins\Comment($this->registry);
            $comment->mapData($this->getRequest()->post);

            $this->{self::MODEL}->addComment($comment);

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $this->processFilterUrl()));
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->getLoader()->language(self::ROUTE);

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $this->getLoader()->model(self::ROUTE);

        if ($this->getRequest()->isRequestMethodPost() &&
            $this->validateForm() &&
            !empty($comment_id = $this->getRequest()->issetGet('comment_id'))
        ) {
            $comment = new Plugins\Comment($this->registry);
            $comment->mapData($this->getRequest()->post);
            $this->{self::MODEL}->editComment($comment);

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $this->processFilterUrl()));
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->getLoader()->language(self::ROUTE);

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));

        $this->getLoader()->model(self::ROUTE);

        if (isset($this->getRequest()->post['selected']) && $this->validateDelete()) {
            foreach ($this->getRequest()->post['selected'] as $comment_id) {
                $this->model_catalog_review->deleteReview($comment_id);
            }

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $this->processFilterUrl()));
        }

        $this->getList();
    }

    protected function getList()
    {
        $url = $this->processFilterUrl();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken())
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $url)
        );

        $data['add'] = $this->getUrl()->link(self::ROUTE.'/add', 'user_token=' . $this->getUserToken() . $url);
        $data['delete'] = $this->getUrl()->link(self::ROUTE.'/delete', 'user_token=' . $this->getUserToken() . $url);
        $data['enabled'] = $this->getUrl()->link(self::ROUTE.'/enable', 'user_token=' . $this->getUserToken() . $url);
        $data['disabled'] = $this->getUrl()->link(self::ROUTE.'/disable', 'user_token=' . $this->getUserToken() . $url);

        $data['reviews'] = array();

        $filter_data = $this->processFilter();

        $comments_total = $this->{self::MODEL}->getCommentsTotal($filter_data);
        $results = $this->{self::MODEL}->getComments($filter_data);

        $this->getLoader()->model('tool/image');

        foreach ($results as $result) {
            if (!empty($result['avatar'])) {
                $image = $this->model_tool_image->resize("avatars/comments/{$result['avatar']}", 60, 60);
            } else {
                $image = $this->model_tool_image->resize('profile.png', 60, 60);
            }

            $comment = new Plugins\Comment($this->registry);
            $comment->mapData($result);
            $data['comments'][] = array(
                'comment' => $comment,
                'edit' => $this->getUrl()->link(self::ROUTE.'/edit', 'user_token=' . $this->getUserToken() . '&comment_id=' . $result['id'] . $url)
            );
        }

        $data['user_token'] = $this->getUserToken();

        $this->processWarningMessage($data);
        $this->processSuccessMessage($data);
        $this->processSelected($data);

        $url = $this->processFilterUrl();

//        $data['sort_product'] = $this->url->link('catalog/review', 'user_token=' . $this->getUserToken() . '&sort=pd.name' . $url, true);
//        $data['sort_author'] = $this->url->link('catalog/review', 'user_token=' . $this->getUserToken() . '&sort=r.author' . $url, true);
//        $data['sort_rating'] = $this->url->link('catalog/review', 'user_token=' . $this->getUserToken() . '&sort=r.rating' . $url, true);
//        $data['sort_status'] = $this->url->link('catalog/review', 'user_token=' . $this->getUserToken() . '&sort=r.status' . $url, true);
//        $data['sort_date_added'] = $this->url->link('catalog/review', 'user_token=' . $this->getUserToken() . '&sort=r.date_added' . $url, true);

        $page = $this->getRequest()->issetGet('page');
        if (empty($page)) {
            $page = 1;
        }

        $pagination = new Pagination();
        $pagination->total = $comments_total;
        $pagination->page = $page;
        $pagination->limit = $this->getConfig()->get('config_limit_admin');
        $pagination->url = $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $url . '&page={page}');
        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->getLanguage()->get('text_pagination'), ($comments_total) ? (($page - 1) * $this->getConfig()->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->getConfig()->get('config_limit_admin')) > ($comments_total - $this->getConfig()->get('config_limit_admin'))) ? $comments_total : ((($page - 1) * $this->getConfig()->get('config_limit_admin')) + $this->getConfig()->get('config_limit_admin')), $comments_total, ceil($comments_total / $this->getConfig()->get('config_limit_admin')));

        $data = array_merge($data, $this->processFilter());

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view(self::ROUTE.'_list', $data));
    }

    protected function getForm()
    {

        $this->getDocument()->addScript('view/javascript/jquery/jquery-bar-rating/jquery.barrating.min.js');
        $this->getDocument()->addStyle('view/javascript/jquery/jquery-bar-rating/themes/fontawesome-stars.css');

        $data['text_form'] = !isset($this->getRequest()->get['comment_id']) ? $this->getLanguage()->get('text_add') : $this->getLanguage()->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

//        if (isset($this->error['product'])) {
//            $data['error_product'] = $this->error['product'];
//        } else {
//            $data['error_product'] = '';
//        }

        if (isset($this->error['author'])) {
            $data['error_author'] = $this->error['author'];
        } else {
            $data['error_author'] = '';
        }

        if (isset($this->error['text'])) {
            $data['error_text'] = $this->error['text'];
        } else {
            $data['error_text'] = '';
        }

        if (isset($this->error['rating'])) {
            $data['error_rating'] = $this->error['rating'];
        } else {
            $data['error_rating'] = '';
        }

        $url = $this->processFilterUrl();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken())
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $url)
        );

        if (!isset($this->request->get['comment_id'])) {
            $data['action'] = $this->getUrl()->link(self::ROUTE.'/add', 'user_token=' . $this->getUserToken() . $url);
        } else {
            $data['action'] = $this->getUrl()->link(self::ROUTE.'/edit', 'user_token=' . $this->getUserToken() . '&comment_id=' . $this->getRequest()->get['comment_id'] . $url);
        }

        $data['cancel'] = $this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $url);

        if (isset($this->request->get['comment_id']) && ($this->getRequest()->server['REQUEST_METHOD'] != 'POST')) {
            $comment = $this->{self::MODEL}->getComment($this->getRequest()->get['comment_id']);
        }

        $data['user_token'] = $this->getUserToken();

        if (isset($this->request->post['author'])) {
            $data['author'] = $this->request->post['author'];
        } elseif (!empty($comment)) {
            $data['author'] = $comment['author'];
        } else {
            $data['author'] = '';
        }

        if (isset($this->request->post['text'])) {
            $data['text'] = $this->request->post['text'];
        } elseif (!empty($comment)) {
            $data['text'] = $comment['text'];
        } else {
            $data['text'] = '';
        }

        if (isset($this->request->post['rating'])) {
            $data['rating'] = $this->request->post['rating'];
        } elseif (!empty($comment)) {
            $data['rating'] = $comment['rating'];
        } else {
            $data['rating'] = '';
        }

        if (isset($this->request->post['date_added'])) {
            $data['date_added'] = $this->request->post['date_added'];
        } elseif (!empty($comment)) {
            $data['date_added'] = ($comment['date_added'] != '0000-00-00 00:00' ? $comment['date_added'] : '');
        } else {
            $data['date_added'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($comment)) {
            $data['status'] = $comment['status'];
        } else {
            $data['status'] = '';
        }

        $this->getLoader()->model('tool/image');

        if (!empty($comment['avatar'])) {
            $data['avatar'] = $this->model_tool_image->resize("avatars/comments/{$comment['avatar']}", 300, 300);
        } else {
            $data['avatar'] = $this->model_tool_image->resize('profile.png', 300, 300);
        }

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view(self::ROUTE.'_form', $data));
    }

    protected function validateForm()
    {
        if (!$this->getUser()->hasPermission('modify', self::ROUTE)) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        if ((utf8_strlen($this->getRequest()->post['author']) < 3) || (utf8_strlen($this->getRequest()->post['author']) > 64)) {
            $this->error['author'] = $this->getLanguage()->get('error_author');
        }

        if (utf8_strlen($this->getRequest()->post['text']) < 1) {
            $this->error['text'] = $this->getLanguage()->get('error_text');
        }

        if (!isset($this->getRequest()->post['rating']) || $this->getRequest()->post['rating'] < 0 || $this->getRequest()->post['rating'] > 5) {
            $this->error['rating'] = $this->getLanguage()->get('error_rating');
        }

        return !$this->error;
    }

    public function enable()
    {
        $this->getLoader()->language(self::ROUTE);
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model('catalog/review');
        if (isset($this->getRequest()->post['selected']) && $this->validateEnable()) {
            $comment_ids = $this->getRequest()->post['selected'];
            $this->{self::MODEL}->massStatusChange($comment_ids, true);
            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');
            $url = '';
            if (isset($this->getRequest()->get['page'])) {
                $url .= '&page=' . $this->getRequest()->get['page'];
            }
            if (isset($this->getRequest()->get['sort'])) {
                $url .= '&sort=' . $this->getRequest()->get['sort'];
            }
            if (isset($this->getRequest()->get['order'])) {
                $url .= '&order=' . $this->getRequest()->get['order'];
            }
            $this->getResponse()->redirect($this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $url));
        }
        $this->getList();
    }

    public function disable()
    {
        $this->getLoader()->language(self::ROUTE);
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model(self::ROUTE);
        if (isset($this->getRequest()->post['selected']) && $this->validateDisable()) {
            $comment_ids = $this->getRequest()->post['selected'];
            $this->{self::MODEL}->massStatusChange($comment_ids, false);
            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $url = '';
            if (isset($this->getRequest()->get['page'])) {
                $url .= '&page=' . $this->getRequest()->get['page'];
            }
            if (isset($this->getRequest()->get['sort'])) {
                $url .= '&sort=' . $this->getRequest()->get['sort'];
            }
            if (isset($this->getRequest()->get['order'])) {
                $url .= '&order=' . $this->getRequest()->get['order'];
            }
            $this->getResponse()->redirect($this->getUrl()->link(self::ROUTE, 'user_token=' . $this->getUserToken() . $url));
        }
        $this->getList();
    }

    protected function validateEnable()
    {
        if (!$this->getUser()->hasPermission('modify', self::ROUTE)) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return !$this->error;
    }

    protected function validateDisable()
    {
        if (!$this->getUser()->hasPermission('modify', self::ROUTE)) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return !$this->error;
    }

    protected function validateDelete()
    {
        if (!$this->getUser()->hasPermission('modify', self::ROUTE)) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return !$this->error;
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

        return [
            'filter_route' => $filter_route,
            'filter_author' => $filter_author,
            'filter_status' => $filter_status,
            'filter_date_added' => $filter_date_added,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->getConfig()->get('config_limit_admin'),
            'limit' => $this->getConfig()->get('config_limit_admin')
        ];
    }

}