<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Ninja\FormBuilder;

class Input extends Control
{
	const TYPE_TEXT = 'text';
	const TYPE_NUMBER = 'number';
	const TYPE_RADIO = 'radio';
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_DATETIME = 'datetime';
	const TYPE_DATE = 'date';
	const TYPE_TIME = 'time';
	//TODO: Add more types

	private $__type;  //for input attribute type=""

    /**
     * Control constructor.
     * @param $type
     * @param $name
     * @param $label
     * @param $id
     * @param bool $is_error
     * @param $error_text
     */
    public function __construct($type, $name, $label, $id, $is_error = false, $error_text = '')
    {
    	parent::__construct($name, $label, $id, $is_error, $error_text);
        $this->__type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->__type;
    }

    /**
     * @param mixed $__type
     */
    public function setType($__type)
    {
        $this->__type = $__type;
    }

}