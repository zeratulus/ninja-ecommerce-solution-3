<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 15.09.19
 * Time: 21:37
 */

namespace Support;

class Project extends CustomItem {

    protected $_id;
    protected $_title;
    protected $_description;
    protected $_created;
    protected $_status;
    protected $_public;
    protected $_color;
    protected $_icon;
    protected $_timezone;

    public static $colorsList = array(
        'default-color',
        'primary-color',
        'warning-color',
        'success-color',
        'warm-flame-gradient',
        'lady-lips-gradient',
        'amy-crisp-gradient',
        'mean-fruit-gradient',
        'ripe-malinka-gradient',
        'morpheus-den-gradient',
        'young-passion-gradient',
    );

    public static $iconsList = array(
        'fa-code',
        'fa-database',
        'fa-globe',
        'fa-calendar',
        'fa-server',
        'fa-bell',
        'fa-info',
        'fa-dollar',
        'fa-euro',
        'fa-compass',
        'fa-comments',
        'fa-cogs',
        'fa-cog'
    );

    public static $iconsConfigList = array(
        'code',
        'database',
        'globe',
        'calendar',
        'server',
        'bell',
        'info',
        'dollar',
        'euro',
        'compass',
        'comments',
        'cogs',
        'cog'
    );

    /**
     * Project constructor.
     * @param \Registry $registry
     */
    public function __construct(\Registry $registry)
    {
        parent::__construct($registry);
    }

    public function mapData(array $data)
    {
        //Required
        $keys = array(
            'id',
            'title',
            'description',
            'created',
            'status',
            'public',
            'color',
            'icon',
            'timezone'
        );

        $this->mapper($data, $keys);
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
     * @return string
     */
    public function getDescriptionHtml(): string
    {
        return html_entity_decode($this->getDescription());
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->_description = $description;
    }

    /**
     * MySQL TIMESTAMP
     * @return string
     */
    public function getCreated(): string
    {
        return $this->_created;
    }

    public function getCreatedCounter(): string
    {
        return str_replace('-', '/', $this->getCreated());
    }

    public function getCreatedString(): string
    {
        return $this->formatDatetimeAsLongString($this->getCreated());
    }

    /**
     * MySQL TIMESTAMP
     * @param string $created
     */
    public function setCreated(string $created)
    {
        $this->_created = $created;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->_status;
    }

    /**
     * @param bool  $status
     */
    public function setStatus(bool $status)
    {
        $this->_status = $status;
    }

    /**
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->_public;
    }

    /**
     * @param bool $public
     */
    public function setIsPublic(bool $public)
    {
        $this->_public = $public;
    }

    /**
     * @return int
     */
    public function getColor(): int
    {
        return $this->_color;
    }

    /**
     * @param int $color
     */
    public function setColor(int $color)
    {
        $this->_color = $color;
    }

    /**
     * @return string
     */
    public function getColorCss(): string
    {
        return ' ' . self::$colorsList[(!empty($this->getColor()) ? $this->getColor() : 0)];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->_icon;
    }

    /**
     * @return string
     */
    public function getIconCss(): string
    {
        return ' ' .self::$iconsList[(!empty($this->getIcon()) ? $this->getIcon() : 0)];
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon)
    {
        $this->_icon = $icon;
    }

    /**
     * @return int
     */
    public function getTimezone(): int
    {
        return $this->_timezone;
    }

    /**
     * @param int $timezone
     */
    public function setTimezone(int $timezone)
    {
        $this->_timezone = $timezone;
    }

    /**
     * Generate URL for project tickets
     * @return string
     */
    public function getLink(): string
    {
        return $this->generateLink("support/tickets", "project_id={$this->getId()}", true);
    }

    public function getEditLink(): string
    {
        return $this->generateLink("support/projects/edit", "project_id={$this->getId()}");
    }

    public function getTotalTickets(): int
    {
        $this->controller->load->model('support/support');

        $filter = new namespace\TicketFilter();
        $filter->setProjectId($this->getId());
        $filter->setStatus(\Support\Status::FILTER_ALL);

        return $this->controller->model_support_support->getTicketsTotal($filter);
    }

    public function getCompletedTasks(): int
    {
        $this->controller->load->model('support/support');

        $filter = new namespace\TicketFilter();
        $filter->setProjectId($this->getId());
        $filter->setStatus(\Support\Status::COMPLETED_ID);

        return $this->controller->model_support_support->getTicketsTotal($filter);
    }

    public function getCompletedTasksPercents(): int
    {
        $total = $this->getTotalTickets();
        $completed = $this->getCompletedTasks();
        if ($total > 0) {
            return (int)round($completed * 100 / $total, 0);
        } else {
            return 0;
        }
    }

    public function getProgressTasks(): int
    {
        $this->controller->load->model('support/support');

        $filter = new namespace\TicketFilter();
        $filter->setProjectId($this->getId());
        $filter->setStatus(\Support\Status::PROGRESS_ID);

        return $this->controller->model_support_support->getTicketsTotal($filter);
    }

    public function getProgressTasksPercents(): int
    {
        $total = $this->getTotalTickets();
        $progress = $this->getProgressTasks();
        if ($total > 0) {
            return (int)round($progress * 100 / $total, 0);
        } else {
            return 0;
        }
    }

    public function getNewTasks(): int
    {
        $this->controller->load->model('support/support');

        $filter = new namespace\TicketFilter();
        $filter->setProjectId($this->getId());
        $filter->setStatus(\Support\Status::NEW_ID);

        return $this->controller->model_support_support->getTicketsTotal($filter);
    }

    public function getNewTasksPercents(): int
    {
        $total = $this->getTotalTickets();
        $progress = $this->getNewTasks();
        if ($total > 0) {
            return (int)round($progress * 100 / $total, 0);
        } else {
            return 0;
        }
    }

    public function getCancelledTasks(): int
    {
        $this->controller->load->model('support/support');

        $filter = new namespace\TicketFilter();
        $filter->setProjectId($this->getId());
        $filter->setStatus(\Support\Status::CANCELED_ID);

        return $this->controller->model_support_support->getTicketsTotal($filter);
    }

    public function getCancelledTasksPercents(): int
    {
        $total = $this->getTotalTickets();
        $progress = $this->getCancelledTasks();
        if ($total > 0) {
            return (int)round($progress * 100 / $total, 0);
        } else {
            return 0;
        }
    }

}
