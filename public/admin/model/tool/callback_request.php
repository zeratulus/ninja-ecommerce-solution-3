<?php

class ModelToolCallbackRequest extends Model {

	public function add($data)
	{
		$sql = "INSERT INTO `".DB_PREFIX."callback_request`(`name`, `telephone`, `time`, `comment`, `created`, `processed`, `process_datetime`) VALUES (
			'{$this->getDb()->escape($data['name'])}', 
			'{$this->getDb()->escape($data['telephone'])}', 
			'{$this->getDb()->escape($data['time'])}', 
			'{$this->getDb()->escape($data['comment'])}', 
			'".$this->getDb()->escape(nowMySQLTimestamp())."', 
			'{$data['processed']}', 
			'{$this->getDb()->escape($data['process_datetime'])}');";
		$this->getDb()->query($sql);
		return $this->getDb()->getLastId();
	}

	public function edit($data, $id)
	{
		$sql = "UPDATE `".DB_PREFIX."callback_request` SET `name`='{$data['name']}', `telephone`='{$data['telephone']}', `time`='{$data['time']}', `comment`='{$data['comment']}', `created`='{$data['created']}', `processed`='{$data['processed']}', `process_datetime`='{$data['process_datetime']}' WHERE id={$id};";
		$this->getDb()->query($sql);
		return $this->getDb()->countAffected();
	}

	public function setProcessed(int $id)
	{
		$sql = "UPDATE `".DB_PREFIX."callback_request` SET `processed`='1', `process_datetime`='".nowMySQLTimestamp()."' WHERE id={$id};";
		$this->getDb()->query($sql);
		return $this->getDb()->countAffected();
	}

	public function getRequests($filter)
	{
		$sql = "SELECT * FROM `".DB_PREFIX."callback_request`";

		$sort_data = array(
			'id',
			'name',
			'telephone',
			'time',
			'comment',
			'created',
			'processed',
			'process_datetime'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY created";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		return $this->getDb()->query($sql)->rows;
	}

	public function getRequestsTotal()
	{
		$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "callback_request`";
		return $this->getDb()->query($sql)->row['total'];
	}

	public function getRequest(int $id)
	{
		$sql = "SELECT * FROM `".DB_PREFIX."callback_request` WHERE id = {$id}";
		return $this->getDb()->query($sql)->row;
	}

	public function install()
	{
		$sql = "CREATE TABLE `".DB_PREFIX."callback_request` (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `name` varchar(255) NOT NULL,
		 `telephone` varchar(32) NOT NULL,
		 `time` varchar(5) NOT NULL,
		 `comment` text NOT NULL,
		 `processed` tinyint(1) NOT NULL,
		 `process_datetime` timestamp NULL DEFAULT NULL,
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";

		$this->getDb()->query($sql);
	}

}
