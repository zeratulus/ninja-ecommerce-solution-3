<?php


namespace Ninja;


class NinjaController extends \Controller
{

	public int $language_id;

	public function __construct($registry) {
		parent::__construct($registry);

		//Installation Application bug fix
        $this->language_id = 0;
		if ($this->registry->has('config')) {
			$this->language_id = $this->config->get('config_language_id');
		}
	}

	public function getBrowserDetection(): \BrowserDetection
	{
		return $this->registry->get('browserdetection');
	}
    
	public function getConfig(): \Config
	{
		return $this->registry->get('config');
	}

	public function getCache(): \Cache
	{
		return $this->registry->get('cache');
	}

	public function getCustomer(): \Cart\Customer
	{
		return $this->registry->get('customer');
	}

	public function getDebugBar(): \DebugBar\exStandardDebugBar
	{
		return $this->registry->get('debugbar');
	}

	public function getDocument(): \Document
	{
		return $this->registry->get('document');
	}

	public function getEvent(): \Event
	{
		return $this->registry->get('event');
	}

	public function getLoader(): \Loader
	{
		return $this->registry->get('load');
	}

	public function getRequest(): \Request
	{
		return $this->registry->get('request');
	}

	public function getResponse(): \Response
	{
		return $this->registry->get('response');
	}

	public function getLanguage(): \Language
	{
		return $this->registry->get('language');
	}

	public function getLanguagesList() {
		$this->getLoader()->model('localisation/language');
		return $this->model_localisation_language->getLanguages();
	}

	public function getLog(): \Log
	{
		return $this->registry->get('log');
	}

	public function getSession(): \Session
	{
		return $this->registry->get('session');
	}

	public function getUser(): \Cart\User
	{
		return $this->registry->get('user');
	}

	public function getUrl(): \Url
	{
		return $this->registry->get('url');
	}

	public function getTemplate(): \Template
	{
		return $this->registry->get('template');
	}

	public function getWeight(): \Cart\Weight
	{
		return $this->registry->get('weight');
	}

	public function getLength(): \Cart\Length
	{
		return $this->registry->get('length');
	}

}
