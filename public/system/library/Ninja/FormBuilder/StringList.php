<?php


namespace Ninja\FormBuilder;


class StringList
{
	private $__list = array();

	/**
	 * @param $item string
	 */
	public function add($item)
	{
		if (strpos($item ,' ') !== false) {
			$tmp_items = explode(' ', $item);
			foreach ($tmp_items as $tmp) {
				$this->insert($tmp);
			}
		} else {
			$this->insert($item);
		}
	}

	/**
	 * @param $item string
	 */
	public function remove($item)
	{
		if (key_exists($item, $this->__list)) {
			unset($this->__list[$item]);
		}
	}

	private function insert($item)
	{
		if (!key_exists($item, $this->__list)) {
			$this->__list[$item] = $item;
		}
	}

	public function getList()
	{
		return $this->__list;
	}

	/**
	 * @return string
	 */
	public function asString()
	{
		return $this->__toString();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return implode(' ', $this->__list);
	}

}