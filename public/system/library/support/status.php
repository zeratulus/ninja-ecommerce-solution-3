<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 16.09.19
 * Time: 19:38
 */

namespace Support;

class Status extends CustomItem {

    protected $_id;
    protected $_project_id;
    protected $_title;
    protected $_color;

    const FILTER_ALL = 0;
    const NEW_ID = 1;
    const PROGRESS_ID = 2;
    const COMPLETED_ID = 3;
    const CANCELED_ID = 4;

    public function __construct(\Registry $registry)
    {
        parent::__construct($registry);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->_id = $id;
    }

    /**
     * @return int
     */
    public function getProjectId(): int
    {
        return $this->_project_id;
    }

    /**
     * @param int $project_id
     */
    public function setProjectId(int $project_id)
    {
        $this->_project_id = $project_id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->_title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->_color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color)
    {
        $this->_color = $color;
    }

    public function mapData(array $data)
    {
        $map = array(
            'id',
            'project_id',
            'title',
            'color',
        );

        $this->mapper($data, $map);
    }

    function getColorCss() {
//        return \Support\Project::$colorsList[$this->getColor()];
        return $this->getColor();
    }

}