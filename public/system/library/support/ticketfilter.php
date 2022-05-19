<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 25.09.19
 * Time: 5:38
 */

namespace Support;

class TicketFilter {
    private $_project_id = -1;
    private $_category_id = -1;
    private $_created_by_uid = -1;
    private $_delegated_to_uid = -1;
    private $_status = -1;
    private $_start;
    private $_finish;
    private $_title = '';
    private $_limit = -1;
    private $_page = 0;
    private $_is_one_page = false;
    private $_sort_field = 'id';
    private $_sort_direction = 'ASC';
    private $_filter = false;

    const SORT_ID = 'id';
    const SORT_TITLE = 'title';
    const SORT_STATUS = 'status';
    const SORT_CREATED_USER = 'created_by_uid';
    const SORT_DELEGATED_USER = 'delegated_by_uid';
    const SORT_CATEGORY = 'category_id';

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
        $this->setFilter(true);
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->_category_id;
    }

    /**
     * @param int $category_id
     */
    public function setCategoryId(int $category_id)
    {
        $this->_category_id = $category_id;
        $this->setFilter(true);
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
        $this->setFilter(true);
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
        $this->setFilter(true);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->_status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->_status = $status;
        $this->setFilter(true);
    }

    /**
     * @return string Timestamp
     */
    public function getStart(): string
    {
        return $this->_start;
    }

    /**
     * @param mixed $start
     */
    public function setStart(string $start)
    {
        $this->_start = $start;
        $this->setFilter(true);
    }

    /**
     * @return mixed
     */
    public function getFinish(): string
    {
        return $this->_finish;
    }

    /**
     * @param mixed $finish
     */
    public function setFinish(string $finish)
    {
        $this->_finish = $finish;
        $this->setFilter(true);
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
        $this->setFilter(true);
    }

    /**
     * @return bool
     */
    public function isFilter(): bool
    {
        return $this->_filter;
    }

    /**
     * @param bool $filter
     */
    private function setFilter(bool $filter)
    {
        $this->_filter = $filter;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->_limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->_limit = $limit;
        $this->setFilter(true);
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->_page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->_page = $page;
        $this->setFilter(true);
    }

    /**
     * @return bool
     */
    public function isOnePage(): bool
    {
        return $this->_is_one_page;
    }

    /**
     * @param bool $is_one_page
     */
    public function setIsOnePage(bool $is_one_page)
    {
        $this->_is_one_page = $is_one_page;
    }

    /**
     * @return string
     */
    public function getSortField(): string
    {
        return $this->_sort_field;
    }

    /**
     * @param string $sort_field
     */
    public function setSortField(string $sort_field)
    {
        $this->_sort_field = $sort_field;
    }

    /**
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->_sort_direction;
    }

    /**
     * @param string $sort_direction
     */
    public function setSortDirection(string $sort_direction)
    {
        $this->_sort_direction = $sort_direction;
    }



}