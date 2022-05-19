<?php

namespace Plugins;

class Comment extends \CustomItem
{
    private int $_id = -1;
    private int $_parent_id = -1;
    private int $_customer_id = -1;
    private string $_author = '';
    private string $_text = '';
    private int $_rating = 0;
    private bool $_status = true;
    private string $_date_added = '';
    private string $_date_modified = '';
    private string $_route = '';
    private string $_avatar = '';

    public function mapData(array $data): void
    {
        $map = [
            'id',
            'parent_id',
            'customer_id',
            'author',
            'text',
            'rating',
            'status',
            'date_added',
            'date_modified',
            'route',
            'avatar'
        ];
        $this->mapper($data, $map);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * @param int $_id
     */
    public function setId(int $_id): void
    {
        $this->_id = $_id;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->_parent_id;
    }

    /**
     * @param int|null $_parent_id
     */
    public function setParentId(?int $_parent_id): void
    {
        $this->_parent_id = $_parent_id;
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->_customer_id;
    }

    /**
     * @param int|null $_customer_id
     */
    public function setCustomerId(?int $_customer_id): void
    {
        $this->_customer_id = $_customer_id;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->_author;
    }

    /**
     * @param string $_author
     */
    public function setAuthor(string $_author): void
    {
        $this->_author = $_author;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->_text;
    }

    /**
     * @param string $_text
     */
    public function setText(string $_text): void
    {
        $this->_text = $_text;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->_rating;
    }

    /**
     * @param int $_rating
     */
    public function setRating(int $_rating): void
    {
        $this->_rating = $_rating;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->_status;
    }

    /**
     * @param bool $_status
     */
    public function setStatus(bool $_status): void
    {
        $this->_status = $_status;
    }

    /**
     * @return string
     */
    public function getDateAdded(): string
    {
        return $this->_date_added;
    }

    /**
     * @param string $_date_added
     */
    public function setDateAdded(string $_date_added): void
    {
        $this->_date_added = $_date_added;
    }

    /**
     * @return string
     */
    public function getDateModified(): string
    {
        return $this->_date_modified;
    }

    /**
     * @param string $_date_modified
     */
    public function setDateModified(string $_date_modified): void
    {
        $this->_date_modified = $_date_modified;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->_route;
    }

    /**
     * @param string $_route
     */
    public function setRoute(string $_route): void
    {
        $this->_route = $_route;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->_avatar;
    }

    /**
     * @param string $_avatar
     */
    public function setAvatar(string $_avatar): void
    {
        $this->_avatar = $_avatar;
    }



}