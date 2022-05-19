<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 21.07.19
 * Time: 19:33
 */

namespace Tools\Monsters\Forms;

use Tools\Monsters\Language\Twig\FormRenderer;

class Form
{

    private $_registry;

    /**
     * Form action attribute
     * @var string;
     */
    private $_action;


    /**
     * @var string
     */
    private $_method; //default POST

    /**@var  bool
     * (if false then generate first element of form hidden input with autocomplete="off" atribute)
     * (if we have password field in controls array and $this->isAutocomplete() === 'off' then add to password control attr autocomplete="no-password")
     **/
    private $_isAutocomplete;

    private $_controls; // add Controls Class List

    //TODO: buttons
    private $_buttons; //submit, reset, button

    public $controller;

    public function __construct(\Registry &$registry)
    {
        $this->_registry = $registry;
        $this->_controls = new FormRenderer\Controls();
        $this->_method = FormMethod::POST;
        $this->controller = new \Support\Controller($this->_registry);
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->_action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->_action = $action;
    }

    /**
     * @return mixed
     */
    public function getMethod(): string
    {
        return $this->_method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod(string $method)
    {
        $this->_method = $method;
    }

    public function getControls(): FormRenderer\Controls
    {
        $this->_controls;
    }

    public function addControl(string $control_type, string $type, string $name)
    {
        //generate all needed by name
        $id = "input-{$name}";
        $label = $this->controller->getLanguage()->get("text_{$name}");

        $this->_controls->addControl($control_type, $type, $name, $label, $id);
    }

    public function renderForm()
    {
        //TODO: add Class
        $html = "<form action='{$this->getAction()}' class='' method='{$this->getMethod()}' enctype='multipart/form-data'>";

        $controls = $this->getControls();

        foreach ($controls as $control) {
            $value = $this->controller->getRequest()->isset_post($control->getName());

            if ($control->getControlType() == ControlType::INPUT) {
                $html .= '<div class="form-group">
                    <label class="col-sm-2 control-label label-small" for="'.$control->getId().'">'.$control->getLabel().'</label>
                    <div class="col-sm-10">
                      <input type="'.$control->getType().'" name="'.$control->getName().'" value="'.$value.'" placeholder="'.$control->getLabel().'" id="'.$control->getId().'" class="form-control">
                    </div>';
                if ($control->isError()) {
                    $html .= '<div class="text-danger">'.$control->getErrorText().'</div>';
                }
                $html .= '</div>';
            } elseif ($control->getControlType() == ControlType::TEXTAREA) {
                $html .= '<div class="form-group">
                    <label class="col-sm-2 control-label label-small" for="'.$control->getId().'">'.$control->getLabel().'</label>
                    <div class="col-sm-10">
                      <textarea name="'.$control->getName().'" rows="5" placeholder="'.$control->getLabel().'" id="'.$control->getId().'" class="form-control">'.$value.'</textarea>
                    </div>';
                $html .= '</div>';
            } elseif ($control->getControlType() == ControlType::SELECT) {
                $html .= '<div class="form-group">
                    <label class="col-sm-2 control-label label-small" for="'.$control->getId().'">'.$control->getLabel().'</label>
                    <div class="col-sm-10">
                      <select id="'.$control->getId().'" class="form-control"></select>
                    </div>';
                if ($control->isError()) {
                    $html .= '<div class="text-danger">'.$control->getErrorText().'</div>';
                }
                $html .= '</div>';
            }

        }
        $html .= '</form>';
        return $html;
    }

}