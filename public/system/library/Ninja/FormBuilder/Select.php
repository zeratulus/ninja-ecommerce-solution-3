<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Ninja\FormBuilder;

class Select extends Control
{
    private $__options = array();

    /**
     * Control constructor.
     * @param $name
     * @param $label
     * @param $id
     * @param bool $is_error
     * @param $error_text
     */
    public function __construct($name, $label, $id, $is_error = false, $error_text = '')
    {
    	parent::__construct($name, $label, $id, $is_error, $error_text);
    }

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->__options;
	}

	/**
	 * @param array $_options
	 */
	public function setOptions($_options)
	{
		$this->__options = $_options;
	}

	public function addOption($name, $value)
	{
		$this->__options[$name] = $value;
	}

	public function removeOption($name)
	{
		unset($this->__options[$name]);
	}

}