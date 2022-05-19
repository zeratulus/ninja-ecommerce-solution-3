<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Ninja\FormBuilder;

/**
 * Class Control - Base class of all controls on form
 * @package Ninja\FormBuilder
 */

class Control
{
    private $__name;            //for attribute name=""
    private $__placeholder;

    private $__label;           //text for label
    private $__id;              //id for label and control
	private $__classes;         //classes
    private $__is_error;        //if we need draw some error from validation use this flag and $error_text
    private $__error_text;      //text with error message

	private $__required = false;
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
        $this->__name = $name;
        $this->__label = $label;
        $this->__id = $id;
        $this->__is_error = $is_error;
        $this->__error_text = $error_text;

        $this->__classes = new StringList();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->__name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->__name = $name;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder()
    {
        return $this->__placeholder;
    }

    /**
     * @param mixed $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->__placeholder = $placeholder;
    }



    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->__label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->__label = $label;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->__id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->__id = $id;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->__is_error;
    }

    /**
     * @param bool $is_error
     */
    public function setIsError($is_error)
    {
        $this->__is_error = $is_error;
    }

    /**
     * @return mixed
     */
    public function getErrorText()
    {
        return $this->__error_text;
    }

    /**
     * @param mixed $error_text
     */
    public function setErrorText($error_text)
    {
        $this->__error_text = $error_text;
    }

    public function cloneControl()
    {
        return clone $this;
    }

	/**
	 * @param $class string
	 */
	public function addClass($class)
	{
		$this->__classes->add($class);
    }

	/**
	 * @param $class string
	 */
	public function removeClass($class)
	{
		$this->__classes->remove($class);
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->__classes->asString();
	}

	/**
	 * HTML element Required attribute
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->__required;
	}

	/**
	 * HTML element Required attribute
	 * @param bool $required
	 */
	public function setRequired($required)
	{
		$this->__required = $required;
	}


}