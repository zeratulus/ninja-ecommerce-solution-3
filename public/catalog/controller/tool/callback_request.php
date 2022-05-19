<?php

use Ninja\FormBuilder\Form;

class ControllerToolCallbackRequest extends \Ninja\NinjaController {

	private $_route = 'tool/callback_request';
	private $_model = 'model_tool_callback_request';

	public function index()
	{
		$this->getLanguage()->load($this->_route);

		$action = $this->getUrl()->link($this->_route .'/post', '', true);
		$data['action'] = $action;

		$form = new Form($this->registry, $action,Form::METHOD_POST);
		$form->addInput('text', 'name');
		$form->addInput('tel', 'telephone');
		$form->addInput('time', 'time');
		$form->addTextarea('comment');

		$data['form'] = $form->renderWholeForm();

		return $this->getLoader()->view($this->_route, $data);
	}

	private function validate()
	{
		$valid = true;

		if (empty($this->getRequest()->issetPost('name'))) {
			$valid = false;
		}

		if (empty($this->getRequest()->issetPost('telephone'))) {
			$valid = false;
		}

		if (empty($this->getRequest()->issetPost('time'))) {
			$valid = false;
		}

		return $valid;
	}

	public function post()
	{
		$json = array();

		$this->getLanguage()->load($this->_route);

		if ($this->getRequest()->isRequestMethodPost() && $this->validate()) {
			$this->getLoader()->model($this->_route);
			$result = $this->{$this->_model}->add($this->getRequest()->post);

			if ($result) {
				$json['success'] = sprintf($this->getLanguage()->get('text_success'), $result);
			} else {
				$json['error'] = $this->getLanguage()->get('error_request');
			}
		} else {
			$json['error'] = $this->getLanguage()->get('error_request');
		}

		$app = new Tools\Monsters\ApplicationJson($this->registry);
		$app->setOutput($json);
	}

}
