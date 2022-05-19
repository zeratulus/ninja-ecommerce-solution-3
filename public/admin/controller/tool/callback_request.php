<?php

class ControllerToolCallbackRequest extends \Ninja\AdminController {

	private $_route = 'tool/callback_request';
	private $_model = 'model_tool_callback_request';

	public function index()
	{
		$this->getLoader()->model($this->_route);
		$this->getLanguage()->load($this->_route);

		$url = '';

		if (empty($sort = $this->getRequest()->issetGet('sort'))) $sort = 'name';
		$url .= "&sort={$sort}";

		if (empty($order = $this->getRequest()->issetGet('order'))) $order = 'DESC';
		$url .= "&order={$order}";

		if (empty($page = $this->getRequest()->issetGet('page'))) $page = 1;
		$url .= "&page={$page}";

		$limit = $this->getConfig()->get('config_limit_admin');

		$filter = array(
			'sort' => $sort,
			'order' => $order,
			'start'	=> ($page - 1) * $limit,
			'limit'	=> $limit
		);

		$data['requests'] = $this->{$this->_model}->getRequests($filter);

		$requests_total = $this->{$this->_model}->getRequestsTotal();

		$pagination = new Pagination();
		$pagination->total = $requests_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->getUrl()->link($this->_route, 'user_token=' . $this->getUserToken() . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->getLanguage()->get('text_home'),
			'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken(), true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->getLanguage()->get('heading_title'),
			'href' => $this->getUrl()->link($this->_route, 'user_token=' . $this->getUserToken(), true)
		);

		$data['statuses'] = array(
			0 => $this->getLanguage()->get('text_not_processed'),
			1 => $this->getLanguage()->get('text_processed')
		);

		$data['action_process'] = $this->getUrl()->link($this->_route . '/process', "&user_token={$this->getUserToken()}");

		$data['header'] = $this->getLoader()->controller('common/header');
		$data['column_left'] = $this->getLoader()->controller('common/column_left');
		$data['footer'] = $this->getLoader()->controller('common/footer');

		$this->getResponse()->setOutput($this->getLoader()->view($this->_route . '_list', $data));
	}

	public function process()
	{
		if ($request_id = $this->getRequest()->issetGet('request_id')) {
			$this->getLoader()->model($this->_route);
			$this->{$this->_model}->setProcessed($request_id);
		}

		$this->getResponse()->redirect($this->getUrl()->link($this->_route, "&user_token={$this->getUserToken()}"));
	}


}