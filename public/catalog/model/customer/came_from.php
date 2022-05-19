<?php
class ModelCustomerCameFrom extends Model {

	public function addCameFrom($data) {
		$sql = "INSERT IGNORE INTO `".DB_PREFIX."customer_came_from` (`session_id`, `ip`, `customer_id`, `utm_id`, `platform`, 
			`platform_version`, `browser`, `browser_version`, `user_agent`, `screen_detected`, `screen_width`, 
			`screen_height`, `url`, `referer`, `date_added`) VALUES (
				'{$this->getDb()->escape($data['session_id'])}', 
				'{$this->getDb()->escape($data['ip'])}', 
				'{$this->getDb()->escape($data['customer_id'])}', 
				'{$this->getDb()->escape($data['utm_id'])}', 
				'{$this->getDb()->escape($data['platform'])}', 
				'{$this->getDb()->escape($data['platform_version'])}', 
				'{$this->getDb()->escape($data['browser'])}', 
				'{$this->getDb()->escape($data['browser_version'])}', 
				'{$this->getDb()->escape($data['user_agent'])}', 
				'{$this->getDb()->escape($data['screen_detected'])}', 
				'{$this->getDb()->escape($data['screen_width'])}', 
				'{$this->getDb()->escape($data['screen_height'])}', 
				'{$this->getDb()->escape($data['url'])}', 
				'{$this->getDb()->escape($data['referer'])}', 
				'{$this->getDb()->escape($data['date_added'])}'
		)";

		$this->getDb()->query($sql);
		return $this->getDb()->getLastId();
	}

	public function addUtm($data) {
		$sql = "INSERT INTO `".DB_PREFIX."customer_came_from_utm` (`utm_id`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`) VALUES (
				'{$this->getDb()->escape($data['utm_source'])}', 
				'{$this->getDb()->escape($data['utm_medium'])}', 
				'{$this->getDb()->escape($data['utm_campaign'])}', 
				'{$this->getDb()->escape($data['utm_content'])}', 
				'{$this->getDb()->escape($data['utm_term'])}'
		)";

		$this->getDb()->query($sql);
		return $this->getDb()->getLastId();
	}

	public function isScreenDetected($session_id)
	{
		$sql = "SELECT screen_detected FROM `".DB_PREFIX."customer_came_from` WHERE session_id = '{$session_id}'";
		$result = $this->getDb()->query($sql);
		return !empty($result->row) ? (bool)$result->row['screen_detected'] : false;
	}

	public function updateScreen($session_id, $data)
	{
		$sql = "UPDATE `".DB_PREFIX."customer_came_from` SET
			`screen_detected` = '{$this->getDb()->escape($data['screen_detected'])}', 
			`screen_width` = '{$this->getDb()->escape($data['screen_width'])}', 
			`screen_height` = '{$this->getDb()->escape($data['screen_height'])}' 
		 WHERE session_id = '{$session_id}'";
		$result = $this->getDb()->query($sql);
		return $this->getDb()->countAffected();
	}

}
