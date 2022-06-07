<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 15.09.19
 * Time: 23:35
 */

namespace Support;

class Ticket extends CustomItem {

    protected $_id;
    protected $_title;
    protected $_description;
    protected $_start;
    protected $_finish;
    protected $_status;
    protected $_created_by_uid;
    protected $_delegated_to_uid;
    protected $_parent_task_id;
    protected $_project_id;
    protected $_category_id;
    protected $_priority;
    protected $_deadline;
    protected $_created;
    protected $_progress_percents = 0;

    protected $_icon = '';

    public $userCreator = NULL;
    public $userDelegated = NULL;

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

    public function getDescriptionHtml(): string
    {
        return html_entity_decode($this->_description);
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->_description = $description;
    }

    /**
     * @return string Timestamp
     */
    public function getStart(): string
    {
        return $this->_start;
    }

    /**
     * @return string Timestamp YYYY-MM-DD
     */
    public function getStartDate(): string
    {
        return substr($this->_start, 0, 10);
    }

    /**
     * @param string $start
     */
    public function setStart(string $start)
    {
        $this->_start = $start;
    }

    public function getStartDateString()
    {
        return $this->formatDatetimeAsLongString($this->getStart());
    }

    public function getCounterTime()
    {
        return str_replace('-', '/', $this->getStart());
    }

    /**
     * @return string
     */
    public function getFinish(): string
    {
        return $this->_finish;
    }

    /**
     * @return string Timestamp YYYY-MM-DD
     */
    public function getFinishDate(): string
    {
        return substr($this->_start, 0, 10);
    }

    /**
     * @param string $finish
     */
    public function setFinish(string $finish)
    {
        $this->_finish = $finish;
    }

    public function getFinishDateString()
    {
        return $this->formatDatetimeAsLongString($this->getFinish());
    }

    public function getProgressTime(): string
    {
        if ($this->getFinish() != '0000-00-00 00:00:00') {
            $ds = $this->controller->language->get('text_day_short');
            $hs = $this->controller->language->get('text_hour_short');
            $ms = $this->controller->language->get('text_min_short');
            return date_diff(new \DateTime($this->getFinish()), new \DateTime($this->getStart()))->format("%d {$ds} %H {$hs} %I {$ms}");
        } else {
            return '0';
        }
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->_status;
    }

    public function getStatusTitle(): string
    {
        $this->controller->load->model('support/support');
        $statuses = $this->controller->model_support_support->getTicketStatuses();
        return $statuses[$this->getStatus()]->getTitle();
    }

    public function getStatusCss(): string
    {
        $this->controller->load->model('support/support');
        $statuses = $this->controller->model_support_support->getTicketStatuses();
        return $statuses[$this->getStatus()]->getColorCss();
    }

    public function getStatusIcon(): string
    {
        $icon = '';
        if ($this->getStatus() == \Support\Status::PROGRESS_ID) {
            $icon = '<span><i class="fa fa-play"></i></span>';
        } elseif ($this->getStatus() == \Support\Status::COMPLETED_ID) {
            $icon = '<span><i class="fa fa-flag-checkered"></i></span>';
        }
        return $icon;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->_status = $status;
    }

    /**
     * @return int
     */
    public function getCreatedByUid(): int
    {
        return $this->_created_by_uid;
    }

    /**
     * @param int $created_by_uid
     */
    public function setCreatedByUid(int $created_by_uid)
    {
        $this->_created_by_uid = $created_by_uid;
    }

    /**
     * @return int
     */
    public function getDelegatedToUid(): int
    {
        return $this->_delegated_to_uid;
    }

    /**
     * @param int $delegated_to_uid
     */
    public function setDelegatedToUid(int $delegated_to_uid)
    {
        $this->_delegated_to_uid = $delegated_to_uid;
    }

    /**
     * @return int
     */
    public function getParentTaskId(): int
    {
        return $this->_parent_task_id;
    }

    /**
     * @param int $parent_task_id
     */
    public function setParentTaskId(int $parent_task_id)
    {
        $this->_parent_task_id = $parent_task_id;
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
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->_category_id;
    }

    public function getCategoryTitle(): string
    {
        $result = '';
        if ($this->getCategoryId() == -1) {
            $this->controller->language->load('support/ticket');
            $result = $this->controller->language->get('text_no_category');
        } elseif ($this->getCategoryId() > 0) {
            $this->controller->load->model('support/support');
            $categories = $this->controller->model_support_support->getCategoriesByProjectId($this->getProjectId());
            foreach ($categories as $category) {
                if ($category['id'] == $this->getCategoryId()) {
                    $result = $category['title'];
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param int $category_id
     */
    public function setCategoryId(int $category_id)
    {
        $this->_category_id = $category_id;
    }

    /**
     * @return null
     */
    public function getUserCreator()
    {
        return $this->userCreator;
    }

    /**
     * @param null $userCreator
     */
    public function setUserCreator($userCreator)
    {
        $this->userCreator = $userCreator;
    }

    /**
     * @return null
     */
    public function getUserDelegated()
    {
        return $this->userDelegated;
    }

    /**
     * @param null $userDelegated
     */
    public function setUserDelegated($userDelegated)
    {
        $this->userDelegated = $userDelegated;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->_priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority)
    {
        $this->_priority = $priority;
    }

    /**
     * @return string Timestamp
     */
    public function getDeadline(): string
    {
        return $this->_deadline;
    }

    public function getDeadlineDateString()
    {
        return $this->formatDatetimeAsLongString($this->getDeadline());
    }

    /**
     * @param string $deadline Timestamp
     */
    public function setDeadline(string $deadline)
    {
        $this->_deadline = $deadline;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->_created;
    }

    /**
     * @return string Timestamp YYYY-MM-DD
     */
    public function getCreatedDate(): string
    {
        return substr($this->getCreated(), 0, 10);
    }


    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->_created = $created;
    }

    public function getChildIds(): array
    {
        $this->controller->getLoader()->model('support/support');
        return $this->controller->model_support_support->getTicketChildIds($this->getId());
    }

    public function getChildIdsForGant(): string
    {
        $result = '';
        foreach ($this->getChildIds() as $row) {
            $result .= "task-{$row['id']},";
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getProgressPercents(): int
    {
        return $this->_progress_percents;
    }

    /**
     * @param int $progress_percents
     */
    public function setProgressPercents(int $progress_percents)
    {
        $this->_progress_percents = $progress_percents;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->_icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon)
    {
        $this->_icon = $icon;
    }

    public function mapData(array $data)
    {
        $keys = array(
            'id',
            'title',
            'description',
            'start',
            'finish',
            'status',
            'created_by_uid',
            'delegated_to_uid',
            'parent_task_id',
            'project_id',
            'category_id',
            'priority',
            'deadline',
            'created',
        );

        $this->mapper($data, $keys);

        $this->userCreator = $this->getUserInfo($this->getCreatedByUid());
        if ($this->getDelegatedToUid() > 0) {
            $this->userDelegated = $this->getUserInfo($this->getDelegatedToUid());
        }
    }

    public function getTicketHref()
    {
        return $this->generateLink('support/ticket', "&project_id={$this->getProjectId()}&ticket_id={$this->getId()}");
    }

    public function getViewAction(): string
    {
        $href = $this->generateLink('support/ticket', "&project_id={$this->getProjectId()}&ticket_id={$this->getId()}");
        return "<a class='btn btn-floating circle btn-primary' href='{$href}'><i class='fa fa-eye'></i></a>";
    }

    public function getEditAction(): string
    {
        $href = $this->generateLink('support/tickets/edit', "&project_id={$this->getProjectId()}&ticket_id={$this->getId()}");
        return "<a class='btn btn-floating circle btn-primary' href='{$href}'><i class='fa fa-pencil'></i></a>";
    }

}