<?php

class ModelExtensionModuleHybridAuth extends Model {
	//route to module Controller
	private $_route = 'extension/module/hybrid_auth';

	public function install()
	{
		$sql = 'CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'customer_authentication` (
    `customer_id` INT NOT NULL ,
    `provider` VARCHAR(255) NOT NULL ,
    `identifier` TEXT NOT NULL ,
    `web_site_url` TEXT NOT NULL ,
    `profile_url` TEXT NOT NULL ,
    `photo_url` TEXT NOT NULL ,
    `display_name` VARCHAR(255) NOT NULL ,
    `description` TEXT NOT NULL ,
    `first_name` VARCHAR(255) NOT NULL ,
    `last_name` VARCHAR(255) NOT NULL ,
    `gender` VARCHAR(255) NOT NULL ,
    `language` VARCHAR(255) NOT NULL ,
    `age` INT NOT NULL ,
    `birth_day` INT NOT NULL ,
    `birth_month` INT NOT NULL ,
    `birth_year` INT NOT NULL ,
    `email` VARCHAR(255) NOT NULL ,
    `email_verified` BOOLEAN NOT NULL ,
    `phone` VARCHAR(255) NOT NULL ,
    `address` TEXT NOT NULL ,
    `country` VARCHAR(255) NOT NULL ,
    `region` VARCHAR(255) NOT NULL ,
    `city` VARCHAR(255) NOT NULL ,
    `zip` VARCHAR(61) NOT NULL ,
    `date_added` TIMESTAMP NOT NULL
) ENGINE = MyISAM;';

		$this->db->query($sql);

		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->_route);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->_route);

		return true;
	}

}
