<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 21:40
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;

class Tabs
{
    private $tabs = array();

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * @param integer $tab_id
     * @return Tab
     */
    public function getTabByIdx($tab_id)
    {
        return $this->tabs[$tab_id];
    }

    /**
     * @param string $tab_id
     * @param string $title
     */
    public function addTab($tab_id, $title)
    {
        $this->tabs[$tab_id] = new Tab($title, $tab_id);
        return $this->getTabByIdx($tab_id);
    }

}