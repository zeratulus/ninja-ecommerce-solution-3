<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 26.09.19
 * Time: 20:33
 */

namespace Support;

class User extends CustomItem {

    protected $_user_id;
    protected $_user_group_id;
    protected $_group_name;
    protected $_fullname;
    protected $_email;
    protected $_image;
    protected $_code;
    protected $_phone;

    public $permissions;

    const ADMIN_GROUP_ID = 1;

    public function __construct(\Registry $registry)
    {
        parent::__construct($registry);
        $this->permissions = new namespace\Permissions();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_user_id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->_user_id = $id;
    }

    /**
     * @return int
     */
    public function getUserGroupId(): int
    {
        return $this->_user_group_id;
    }

    /**
     * @param int $user_group_id
     */
    public function setUserGroupId(int $user_group_id)
    {
        $this->_user_group_id = $user_group_id;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->_group_name;
    }

    /**
     * @param string $group_name
     */
    public function setGroupName(string $group_name)
    {
        $this->_group_name = $group_name;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->_fullname;
    }

    /**
     * @param string $fullname
     */
    public function setFullName(string $fullname)
    {
        $this->_fullname = $fullname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->_email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->_email = $email;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        if (!empty($this->_image)) {
            return $this->_image;
        } else {
            return 'profile.png';
        }
    }

    /**
     * @param string $image
     */
    public function setImage(string $image)
    {
        $this->_image = $image;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->_code = $code;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->_phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone)
    {
        $this->_phone = $phone;
    }

    public function mapData(array $data)
    {
        $map = array(
            'user_id',
            'user_group_id',
            'group_name',
            'fullname',
            'email',
            'image',
            'code',
            'phone',
        );

        $this->mapper($data, $map);
    }

    public function getAvatar(): string
    {
        if ($this->controller->request->server['HTTPS']) {
            return HTTPS_CATALOG . 'image/' . $this->getImage();
        } else {
            return HTTP_CATALOG . 'image/' . $this->getImage();
        }
    }

    public function loadPermissionsForProject(int $project_id)
    {
        if (!$this->isAdmin()) {
            $this->controller->getLoader()->model('support/support');
            $rights = $this->controller->model_support_support->getUserPermissionsByProject($project_id, $this->getId());
            $this->permissions->setupPermissions($rights);
        } else {
            $this->permissions->setupPermissions(array('permission' => '1111'));
        }
    }

    public function isAdmin(): bool
    {
        if ($this->getUserGroupId() == self::ADMIN_GROUP_ID) {
            return true;
        } else {
            return false;
        }
    }

}