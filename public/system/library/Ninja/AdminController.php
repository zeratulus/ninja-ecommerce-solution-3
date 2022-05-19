<?php


namespace Ninja;


class AdminController extends NinjaController
{

	public function getUserToken()
	{
		if (isset($this->getSession()->data['user_token'])) {
			return $this->getSession()->data['user_token'];
		} else {
			return false;
		}
	}

	public function makeBreadcrumb($title, $href) {
		return array('text' => $title, 'href' => $href);
	}

	public function makeDefaultBreadcrumbs(string $route = '') {
		$breadcrumbs = array();

		$breadcrumbs[] = $this->makeBreadcrumb(
			$this->getLanguage()->get('text_home'),
			$this->getUrl()->link('common/dashboard', "user_token={$this->getUserToken()}")
		);

		if (empty($route)) {
            $route = $this->_route ?? $this->route ?? defined(get_class($this) . '::ROUTE') ? self::ROUTE : '';
        }

		$breadcrumbs[] = $this->makeBreadcrumb(
			$this->getLanguage()->get('heading_title'),
			$this->getUrl()->link($route, "user_token={$this->getUserToken()}")
		);

		return $breadcrumbs;
	}

	public function processSuccessMessage(array &$data)
	{
		if (isset($this->getSession()->data['success'])) {
			$data['success'] = $this->getSession()->data['success'];

			unset($this->getSession()->data['success']);
		} else {
			$data['success'] = '';
		}
	}

	public function processWarningMessage(array &$data)
	{
		if (isset($this->_error['warning'])) {
            $data['error_warning'] = $this->_error['warning'];
        } elseif (isset($this->getSession()->data['warning'])) {
            $data['error_warning'] = $this->getSession()->data['warning'];

            unset($this->session->data['warning']);
		} else {
			$data['error_warning'] = '';
		}
	}

	public function processSelected(array &$data)
	{
		if (!empty($selected = $this->getRequest()->issetPost('selected'))) {
			$data['selected'] = (array)$selected;
		} else {
			$data['selected'] = array();
		}
	}

    public function getFromPostOrConfig(string $key)
    {
        if (empty($result = $this->getRequest()->issetPost($key))) {
            if ($this->getConfig()->has($key)) {
                $result = $this->getConfig()->get($key);
            }
        }
        return $result;
	}

}