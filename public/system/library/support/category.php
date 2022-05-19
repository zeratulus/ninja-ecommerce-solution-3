<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 15.09.19
 * Time: 23:27
 */

namespace Support;

class Category extends CustomItem {

    protected $_id;
    protected $_project_id;
    protected $_title;
    protected $_description;


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
    public function getDescription(): string
    {
        return $this->_description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->_description = $description;
    }

    public function mapData(array $data)
    {
        $map = array(
            'id',
            'project_id',
            'title',
            'description'
        );

        $this->mapper($data, $map);
    }

    public function getTicketsTotal(): int
    {
        $this->controller->load->model('support/support');

        $filter = new TicketFilter($this->_registry);
        $filter->setProjectId($this->getProjectId());
        $filter->setCategoryId($this->getId());

        return $this->controller->model_support_support->getTicketsTotal($filter);
    }

    public function getFilterHref(): string
    {
        return $this->generateLink("support/tickets", "&project_id={$this->controller->getRequest()->isset_get('project_id')}&filter_category={$this->getId()}");
    }

}