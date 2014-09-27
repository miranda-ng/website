<?php

namespace Models;

use InvalidArgumentException;
use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;
use Nette\Object;
use PDOException;
use Traversable;

abstract class BaseModel extends Object {

	/** @var Context */
	protected $database;

	/** @var string */
	protected $tableName;

	/**
	 * Constructor
	 * @param Context $database
	 */
	public function __construct(Context $database) {
		$this->database = $database;

		// Try to get tableName from anotation
		$reflection = $this->getReflection();
		if ($reflection->hasAnnotation("table")) {
			$this->tableName = $reflection->getAnnotation("table");
		}
	}

	/**
	 * Return whole table selection
	 * @param string|NULL table name sufix
	 * @throws InvalidArgumentException
	 * @return Selection
	 */
	protected function getTable($sufix = NULL) {
		if (!$this->tableName) {
			$class = $this->getReflection()->getName();
			throw new InvalidArgumentException("$class::tableName can't be NULL");
		}

		return $this->database->table($this->tableName . $sufix);
	}

	/**
	 * Inserts row in a table.
	 * @param  array|Traversable|Selection array($column => $value)|\Traversable|Selection for INSERT ... SELECT
	 * @return IRow|int|bool Returns IRow or number of affected rows for Selection or table without primary key
	 */
	public function insert($data) {
		return $this->getTable()->insert($data);
	}

	/**
	 * Updates all rows in result set.
	 * Joins in UPDATE are supported only in MySQL
	 * @param  array|Traversable ($column => $value)
	 * @return int number of affected rows
	 */
	public function update($data) {
		return $this->getTable()->update($data);
	}

	/**
	 * Returns row specified by primary key.
	 * @param  mixed primary key
	 * @return IRow or FALSE if there is no such row
	 */
	public function get($key) {
		return $this->getTable()->get($key);
	}

	/**
	 * Adds condition for primary key.
	 * @param  mixed
	 * @return self
	 */
	public function wherePrimary($key) {
		return $this->getTable()->wherePrimary($key);
	}

	/**
	 * Inserts or updates row
	 * @param type $data
	 * @param type $key
	 * @throws PDOException
	 * @return string Id of inserted or updated item
	 */
	protected function save($data, $key) {
		if ($key === NULL) {
			// Insert
			$row = $this->insert($data);
			$key = $row->getPrimary();
		} else {
			// Update
			$this->wherePrimary($key)->update($data);
		}

		return $key;
	}

	/**
	 * Workaround for broken ->count("*") in Nette\Database in grouped selection
	 * @param Selection $selection
	 * @return int number of rows
	 */
	public static function getSelectionCount(Selection $selection) {
		// FIXME: remove when this will work in Nette
		if ($selection->getSqlBuilder()->getGroup()) {
			$query = "SELECT COUNT(*) FROM (" . $selection->getSql() . ") AS _";
			$parameters = $selection->getSqlBuilder()->getParameters();

			$row = $selection->getConnection()->queryArgs($query, $parameters)->fetch();
			return $row[0];
		} else {
			return $selection->count("*");
		}
	}

}
