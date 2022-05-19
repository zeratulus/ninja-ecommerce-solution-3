<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;

class Control
{
    private $control_type;    //input, textarea, select
    private $type;            //for input attribute type=""
    private $name;            //for attribute name=""
    private $placeholder;

    private $label;           //text for label
    private $id;              //id for label and control

    private $isError;         //if we need draw some error from validation use this flag and $error_text
    private $error_text;      //text with error message


    /**
     * Control constructor.
     * @param $control_type
     * @param $type
     * @param $name
     * @param $label
     * @param $id
     * @param bool $isError
     * @param $error_text
     */
    public function __construct($control_type, $type, $name, $label, $id, $isError = false, $error_text = '')
    {
        $this->control_type = $control_type;
        $this->type = $type;
        $this->name = $name;
        $this->label = $label;
        $this->id = $id;
        $this->isError = $isError;
        $this->error_text = $error_text;
    }

    /**
     * @return mixed
     */
    public function getControlType()
    {
        return $this->control_type;
    }

    /**
     * @param mixed $control_type
     */
    public function setControlType($control_type)
    {
        $this->control_type = $control_type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @param mixed $placeholder
     */
    public function setPlaceholder(string $placeholder)
    {
        $this->placeholder = $placeholder;
    }



    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->isError;
    }

    /**
     * @param bool $isError
     */
    public function setIsError($isError)
    {
        $this->isError = $isError;
    }

    /**
     * @return mixed
     */
    public function getErrorText()
    {
        return $this->error_text;
    }

    /**
     * @param mixed $error_text
     */
    public function setErrorText($error_text)
    {
        $this->error_text = $error_text;
    }

    public function cloneControl()
    {
        return $clone = clone $this;
    }

}