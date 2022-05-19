<?php

class ModelToolCallbackRequest extends Model {

	public function add($data)
	{
		$sql = "INSERT INTO `".DB_PREFIX."callback_request`(`name`, `telephone`, `time`, `comment`, `created`, `processed`, `process_datetime`) VALUES (
			'{$this->getDb()->escape($data['name'])}', 
			'{$this->getDb()->escape($data['telephone'])}', 
			'{$this->getDb()->escape($data['time'])}', 
			'{$this->getDb()->escape($data['comment'])}', '".nowMySQLTimestamp()."', '0', 'null');";
		$this->getDb()->query($sql);

		return $this->getDb()->getLastId();
	}
}
