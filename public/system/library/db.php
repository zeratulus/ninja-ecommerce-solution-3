<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* DB class
*/
class DB {
	private $adaptor;
	private DBQueryCache $cache;
	/**** Ninja Studio - OpenCart PHP Debug Bar ****/
	private $_isDebug = false;
	private $_queries = array();

	/**** Ninja Studio - OpenCart PHP Debug Bar ****/

	/**
	 * Constructor
	 *
	 * @param	string	$adaptor
	 * @param	string	$hostname
	 * @param	string	$username
     * @param	string	$password
	 * @param	string	$database
	 * @param	int		$port
	 *
 	*/
	public function __construct(string $adaptor, $hostname, $username, $password, $database, $port = NULL) {
		$class = 'DB\\' . $adaptor;
		//*** Ninja Studio - OpenCart PHP Debug Bar ***
		if (defined('DEV') && ($adaptor == 'mysqli' || $adaptor == 'mpdo')) {
			$this->_isDebug = true;
		}
		//*** Ninja Studio - OpenCart PHP Debug Bar ***
		if (class_exists($class)) {
			$this->adaptor = new $class($hostname, $username, $password, $database, $port);
		} else {
			throw new \Exception('Error: Could not load database adaptor ' . $adaptor . '!');
		}

		$this->cache = new DBQueryCache();
	}

	/**
     * 
     *
     * @param	string	$sql
	 * 
	 * @return \Db\Results|false
     */
	public function query($sql) {
		/**** Ninja Studio - OpenCart PHP Debug Bar ****/
		if ($this->_isDebug) $time = microtime(true);
		/**** Ninja Studio - OpenCart PHP Debug Bar ****/

        if (strpos(utf8_strtolower($sql), 'select') !== false) {
            if ($this->cache->has($sql)) {
                return $this->cache->get($sql);
            } else {
                $result = $this->adaptor->query($sql);
                $this->cache->add($sql, $result);
            }
        } else {
            $result = $this->adaptor->query($sql);
        }

		/**** Ninja Studio - OpenCart PHP Debug Bar ****/
		if ($this->_isDebug) $time = microtime(true) - $time;

		if ($this->_isDebug) $this->_queries[] = array(
			'sql' => $sql,
			'duration' => $time,
		);
		/**** Ninja Studio - OpenCart PHP Debug Bar ****/
		return $result;
	}

	/**
     * 
     *
     * @param	string	$value
	 * 
	 * @return	string
     */
	public function escape($value) {
		return $this->adaptor->escape($value);
	}

	/**
     * 
	 * 
	 * @return	int
     */
	public function countAffected() {
		return $this->adaptor->countAffected();
	}

	/**
     * 
	 * 
	 * @return	int
     */
	public function getLastId() {
		return $this->adaptor->getLastId();
	}
	
	/**
	 * @return	bool
     */	
	public function connected() {
		return $this->adaptor->connected();
	}

    public function arrayToInClause(array $arr): string
    {
        $data = [];
        foreach ($arr as $item) {
            $data[] = "'$item'";
        }
        return implode(',', $data);
	}

    public function getQueries()
    {
        return $this->_queries;
    }

}