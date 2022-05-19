<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 15.09.19
 * Time: 23:49
 */

namespace Support;

class Comment extends CustomItem {

    protected $_id;
    protected $_ticket_id;
    protected $_uid;
    protected $_text;
    protected $_datetime;

    protected $_user;

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
    public function getTicketId(): int
    {
        return $this->_ticket_id;
    }

    /**
     * @param int $ticket_id
     */
    public function setTicketId(int $ticket_id)
    {
        $this->_ticket_id = $ticket_id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->_uid;
    }

    /**
     * @param int $uid
     */
    public function setUserId(int $uid)
    {
        $this->_uid = $uid;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->_text;
    }


    public function getHtml(): string
    {
        return html_entity_decode($this->_text);
    }
    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->_text = $text;
    }

    /**
     * @return string
     */
    public function getDatetime(): string
    {
        return $this->_datetime;
    }

    public function getDateString()
    {
        return $this->formatDatetimeAsLongString($this->getDatetime());
    }

    /**
     * @param string $datetime
     */
    public function setDatetime(string $datetime)
    {
        $this->_datetime = $datetime;
    }


    public function mapData(array $data)
    {
        $map = array(
            'id',
            'ticket_id',
            'uid',
            'text',
            'datetime'
        );

        $this->mapper($data, $map);

        $this->_user = $this->getUserInfo($this->getUserId());
    }

    public function getUser(): namespace\User
    {
        return $this->_user;
    }

}