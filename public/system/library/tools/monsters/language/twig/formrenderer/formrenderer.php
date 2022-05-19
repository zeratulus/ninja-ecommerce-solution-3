<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;

abstract class FormRenderer extends BaseClass
{
    public $languages; //array of languages
    public $tabs;
    //TODO: //render form without tabs only for current language
    public $renderSingleForm = false;

    public function __construct(\Registry $registry)
    {
        parent::__construct($registry);
        $this->languages = $this->model_languages->getLanguages();
        $this->tabs = new Tabs();
    }

    abstract protected function generateLanguagesTabs($tab_id);

    abstract protected function generateFormElements($tab_id);

    public function generateElements($tab_id)
    {
        $html = $this->generateLanguagesTabs($tab_id);
        $html .= $this->generateFormElements($tab_id);
        return $html;
    }
}