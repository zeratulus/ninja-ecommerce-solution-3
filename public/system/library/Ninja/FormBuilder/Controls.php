<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 24.07.19
 * Time: 13:56
 */

namespace Ninja\FormBuilder;


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
	 * @param $control Control
	 */
	public function addControl($control)
    {
        $this->controls[] = $control;
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