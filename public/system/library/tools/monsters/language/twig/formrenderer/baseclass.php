<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 22:10
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;

class BaseClass
{
    /**
     * @var \Registry
     */
    protected $registry;
    /**
     * @var \Loader
     */
    protected $loader;
    /**
     * @var \Request
     */
    protected $request;
    /**
     * @var \Config
     */
    protected $config;

    /**
     * @var \ModelLocalisationLanguage
     */
    protected $model_languages;

    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;

        $this->loader = $this->registry->get('load');
        $this->request = $this->registry->get('request');
        $this->config = $this->registry->get('config');

        $this->loader->model('localisation/language');
        $this->model_languages = $this->registry->get('model_localisation_language');
    }

}