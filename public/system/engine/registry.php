<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Registry class
*/
final class Registry {
	private $data = array();

	/**
     * @param	string	$key
	 * @return	mixed
     */
	public function get($key) {
		return $this->data[$key] ?? null;
	}

    /**
     * 
     *
     * @param	string	$key
	 * @param	$value
     */	
	public function set(string $key, $value) {
		$this->data[$key] = $value;
	}
	
    /**
     * @param	string	$key
	 * @return	bool
     */
	public function has(string $key): bool {
		return isset($this->data[$key]);
	}
}