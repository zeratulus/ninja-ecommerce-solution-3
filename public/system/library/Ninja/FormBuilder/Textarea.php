<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Ninja\FormBuilder;

class Textarea extends Control
{
    private $__cols;
	private $__rows;
    /**
     * Control constructor.
     * @param $type
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

}