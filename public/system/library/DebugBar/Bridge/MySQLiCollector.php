<?php
namespace DebugBar\Bridge;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DebugBarException;

class MySQLiCollector extends DataCollector implements Renderable, AssetProvider
{
	public const NAME = 'mysqli_queries';
	protected $mysqli_queries;

	public function __construct($queries)
	{
		$this->mysqli_queries = $queries;
	}

	public function collect()
	{
		$queries = array();
		$totalExecTime = 0;

		foreach ($this->mysqli_queries as $q) {
			$query = $q; // = array(
//			'sql' => $sql,
//			'duration' => $time,
//		);
			$query['duration_str'] = $this->formatDuration($q['duration']);
			$queries[] = $query;

			$totalExecTime += $q['duration'];
		}

		return array(
			'nb_statements' => count($queries),
			'accumulated_duration' => $totalExecTime,
			'accumulated_duration_str' => $this->formatDuration($totalExecTime),
			'statements' => $queries
		);
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getWidgets()
	{
		return array(
			"database" => array(
				"icon" => "arrow-right",
				"widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
				"map" => self::NAME,
				"default" => "[]"
			),
			"database:badge" => array(
				"map" => self::NAME.".nb_statements",
				"default" => 0
			)
		);
	}

	public function getAssets()
	{
		return array(
			'css' => 'widgets/sqlqueries/widget.css',
			'js' => 'widgets/sqlqueries/widget.js'
		);
	}
	public function query($statement)
	{
		return $this->profileCall('query', $statement, func_get_args());
	}

	protected function profileCall($method, $sql, array $args)
	{
		$result = call_user_func_array(array($this->mysqli_queries, $method), $args);print_r($result);

		return $result;
	}

	public function setQueries($stmt)
	{
		$this->mysqli_queries = $stmt;
	}
}

