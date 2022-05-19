<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;

class Tab
{

    private $title;
    private $tab_id;
    private $controls = array();

    /**
     * Tab constructor.
     * @param $title
     * @param $tab_id
     */

    public function __construct($title, $tab_id)
    {
        $this->title = $title;
        $this->tab_id = $tab_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTabId()
    {
        return $this->tab_id;
    }

    /**
     * @param string $tab_id
     */
    public function setTabId($tab_id)
    {
        $this->tab_id = $tab_id;
    }


    /**
     * @param mixed $idx
     * @return Control
     */
    public function getControlByIdx($idx)
    {
        return $this->controls[$idx];
    }

    /**
     * @return array
     */
    public function getControls()
    {
        return $this->controls;
    }

    /**
     * @param string  $control_type - input, textarea, select
     * @param string  $type - for input attribute type=""
     * @param string  $name - for attribute name=""
     * @param string  $label - text for label
     * @param string  $id - id for label and control
     * @param boolean $isError - if we need draw some error from validation use this flag and $error_text
     * @param string  $error_text - text with error message
     */
    public function addControl($control_type, $type, $name, $label, $id, $isError = false, $error_text = '')
    {
        $this->controls[$id] = new Control($control_type, $type, $name, $label, $id, $isError, $error_text);
    }

}