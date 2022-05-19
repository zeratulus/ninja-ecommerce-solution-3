<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 19.10.19
 * Time: 15:16
 */

namespace Support;

class Permissions {

    private $isRead = false;
    private $isWrite = false;
    private $isDelete = false;
    private $isCreate = false;

    public function setupPermissions(array $permissions_db_row)
    {
        $permissions = $permissions_db_row['permission'];
        $this->isRead = isset($permissions[0]) ? (bool)$permissions[0] : 0;
        $this->isWrite = isset($permissions[1]) ? (bool)$permissions[1] : 0;
        $this->isDelete = isset($permissions[2]) ? (bool)$permissions[2] : 0;
        $this->isCreate = isset($permissions[3]) ? (bool)$permissions[3] : 0;
    }

    public function hasPermission()
    {
        return ($this->isCreate() || $this->isDelete() || $this->isRead() || $this->isWrite());
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->isRead;
    }

    /**
     * @return bool
     */
    public function isWrite(): bool
    {
        return $this->isWrite;
    }

    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->isDelete;
    }

    /**
     * @return bool
     */
    public function isCreate(): bool
    {
        return $this->isCreate;
    }

}