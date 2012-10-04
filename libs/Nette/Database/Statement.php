<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Database
 */



/**
 * Represents a prepared statement / result set.
 *
 * @author     David Grudl
 *
 * @property-read NConnection $connection
 * @property-write $fetchMode
 * @package Nette\Database
 */
class NStatement extends PDOStatement
{
	/** @var NConnection */
	private $connection;

	/** @var float */
	private $time;

	/** @var array */
	private $types;



	protected function __construct(NConnection $connection)
	{
		$this->connection = $connection;
		$this->setFetchMode(PDO::FETCH_CLASS, 'NRow', array($this));
	}



	/**
	 * @return NConnection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * Executes statement.
	 * @param  array
	 * @return NStatement  provides a fluent interface
	 */
	public function execute($params = array())
	{
		static $types = array('boolean' => PDO::PARAM_BOOL, 'integer' => PDO::PARAM_INT,
			'resource' => PDO::PARAM_LOB, 'NULL' => PDO::PARAM_NULL);

		foreach ($params as $key => $value) {
			$type = gettype($value);
			$this->bindValue(is_int($key) ? $key + 1 : $key, $value, isset($types[$type]) ? $types[$type] : PDO::PARAM_STR);
		}

		$time = microtime(TRUE);
		try {
			parent::execute();
		} catch (PDOException $e) {
			$e->queryString = $this->queryString;
			throw $e;
		}
		$this->time = microtime(TRUE) - $time;
		$this->connection->__call('onQuery', array($this, $params)); // $this->connection->onQuery() in PHP 5.3

		return $this;
	}



	/**
	 * Fetches into an array where the 1st column is a key and all subsequent columns are values.
	 * @return array
	 */
	public function fetchPairs()
	{
		return $this->fetchAll(PDO::FETCH_KEY_PAIR); // since PHP 5.2.3
	}



	/**
	 * Normalizes result row.
	 * @param  array
	 * @return array
	 */
	public function normalizeRow($row)
	{
		foreach ($this->detectColumnTypes() as $key => $type) {
			$value = $row[$key];
			if ($value === NULL || $value === FALSE || $type === IReflection::FIELD_TEXT) {

			} elseif ($type === IReflection::FIELD_INTEGER) {
				$row[$key] = is_float($tmp = $value * 1) ? $value : $tmp;

			} elseif ($type === IReflection::FIELD_FLOAT) {
				$row[$key] = (string) ($tmp = (float) $value) === $value ? $tmp : $value;

			} elseif ($type === IReflection::FIELD_BOOL) {
				$row[$key] = ((bool) $value) && $value !== 'f' && $value !== 'F';

			} elseif ($type === IReflection::FIELD_DATETIME || $type === IReflection::FIELD_DATE || $type === IReflection::FIELD_TIME) {
				$row[$key] = new NDateTime53($value);

			}
		}

		return $this->connection->getSupplementalDriver()->normalizeRow($row, $this);
	}



	private function detectColumnTypes()
	{
		if ($this->types === NULL) {
			$this->types = array();
			if ($this->connection->getSupplementalDriver()->isSupported(ISupplementalDriver::SUPPORT_COLUMNS_META)) { // workaround for PHP bugs #53782, #54695
				$col = 0;
				while ($meta = $this->getColumnMeta($col++)) {
					if (isset($meta['native_type'])) {
						$this->types[$meta['name']] = NDatabaseHelpers::detectType($meta['native_type']);
					}
				}
			}
		}
		return $this->types;
	}



	/**
	 * @return float
	 */
	public function getTime()
	{
		return $this->time;
	}



	/********************* misc tools ****************d*g**/



	/**
	 * Displays complete result set as HTML table for debug purposes.
	 * @return void
	 */
	public function dump()
	{
		NDatabaseHelpers::dumpResult($this);
	}



	/********************* NObject behaviour ****************d*g**/



	/**
	 * @return NClassReflection
	 */
	public function getReflection()
	{
		return new NClassReflection($this);
	}



	public function __call($name, $args)
	{
		return NObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return NObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return NObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return NObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		NObjectMixin::remove($this, $name);
	}

}
