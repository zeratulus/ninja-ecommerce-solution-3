<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 24.07.19
 * Time: 13:56
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;


class Controls
{
    private $controls = array();

    /**
     * @return array
     */
    public function getControls()
    {
        return $this->controls;
    }

    /**
     * @param array $controls
     */
    public function setControls($controls)
    {
        $this->controls = $controls;
    }

    /**
     *  @return Control
     */
    public function addControl($control_type, $type, $name, $label, $id, $isError = false, $error_text = '')
    {
        $this->controls[] = new Control($control_type, $type, $name, $label, $id, $isError, $error_text);
    }

    /**
     *  @return Control
     */
    public function getControlByIdx($idx)
    {
        return $this->controls[$idx];
    }


    //TODO: Get Control By name

}