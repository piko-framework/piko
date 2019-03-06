<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
namespace piko;

use PDO;

/**
 * DbRecord reprensents a database table row.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class DbRecord extends Model
{
    const TYPE_INT = PDO::PARAM_INT;
    const TYPE_STRING = PDO::PARAM_STR;
    const TYPE_BOOL = PDO::PARAM_BOOL;

    /**
     * @var Db The database instance.
     */
    protected $db;

    /**
     * @var string The name of the table.
     */
    protected $tableName = '';

    /**
     * @var array A name-value pair that describes the structure of the table.
     * eg. ['id' => self::TYPE_INT, 'name' => 'id' => self::TYPE_STRING]
     */
    protected $schema = [];

    /**
     * @var string The name of the primary key. Default to 'id'.
     */
    protected $primaryKey = 'id';

    /**
     * Constructor
     *
     * @param number $id The value of the row primary key in order to load the row imediately.
     * @param array $config An array of configuration.
     */
    public function __construct($id = 0, $config = [])
    {
        $db = Piko::get('db');

        if ($db === null) {
            throw \RuntimeException("No db instance found. You must set a db instance with Piko::set('db', \$db).");
        }

        if (!$db instanceof PDO) {
            throw \RuntimeException('Db must be instance of \PDO.');
        }

        $this->db = $db;

        if ((int) $id > 0) {
            $this->load((int) $id);
        }

        parent::__construct($config);
    }

    /**
     * Check if column name is defined in the table schema.
     * @param string $name
     * @throws \RuntimeException
     * @see self::$schema
     */
    protected function checkColumn($name)
    {
        if (!isset($this->schema[$name])) {
            throw new \RuntimeException("$name is not in the table schema.");
        }
    }

    /**
     * {@inheritDoc}
     * @see Model::__get()
     */
    public function __get($attribute)
    {
        $this->checkColumn($attribute);

        return parent::__get($attribute);
    }

    /**
     * {@inheritDoc}
     * @see Model::__set()
     */
    public function __set($attribute, $value)
    {
        $this->checkColumn($attribute);

        parent::__set($attribute, $value);
    }

    /**
     * Load row data.
     * @param number $id The value of the row primary key.
     * @throws \RuntimeException
     */
    public function load($id = 0)
    {
        $st = $this->db->prepare('SELECT * FROM `' . $this->tableName . '` WHERE `' . $this->primaryKey . '` = ?');
        $st->bindParam(1, $id, PDO::PARAM_INT);
        $st->execute();
        $data = $st->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \RuntimeException("Error while trying to load item {$id}");
        }

        $this->data = $data;
    }

    /**
     * Method called before a save action.
     * @param boolean $insert If the row is a new record, the value will be true, otherwise, false.
     * @return boolean
     */
    protected function beforeSave($insert)
    {
        $this->trigger('beforeSave', [$insert]);

        return true;
    }

    /**
     * Method called before a delete action.
     * @return boolean
     */
    protected function beforeDelete()
    {
        $this->trigger('beforeDelete');

        return true;
    }

    /**
     * Method called after a save action.
     */
    protected function afterSave()
    {
        $this->trigger('afterSave');
    }

    /**
     * Method called after a delete action.
     */
    protected function afterDelete()
    {
        $this->trigger('afterDelete');
    }

    /**
     * Save this record into the table.
     * @throws \RuntimeException
     * @return boolean
     */
    public function save()
    {
        foreach ($this->data as $key => $value) {
            $this->checkColumn($key);
        }

        $insert = empty($this->data[$this->primaryKey]) ? true : false;

        if (!$this->beforeSave($insert)) {
            return false;
        }

        $cols = array_keys($this->data);
        $valueKeys = [];

        if ($insert) {
            foreach ($cols as $key) {
                $valueKeys[] = ':' . $key;
            }

            $query = 'INSERT INTO `' . $this->tableName . '` (' . implode(', ', $cols) . ')';
            $query .= ' VALUES (' . implode(', ', $valueKeys) . ')';
        } else {
            foreach ($cols as $key) {
                $valueKeys[] = $key . '= :' . $key;
            }

            $query = 'UPDATE `' . $this->tableName . '` SET ' . implode(', ', $valueKeys);
            $query .= ' WHERE ' . $this->primaryKey . ' = ' . (int) $this->data[$this->primaryKey];
        }

        $st = $this->db->prepare($query);

        if ($st === false) {
            $error = $this->db->errorInfo();

            throw new \RuntimeException("Query '$query' failed with error {$error[0]} : {$error[2]}");
        }

        foreach ($this->data as $key => $value) {
            $st->bindValue(':' . $key, $value, $this->schema[$key]);
        }

        if ($st->execute() === false) {
            $error = $st->errorInfo();
            throw new \RuntimeException("Query failed with error {$error[0]} : {$error[2]}");
        }

        if ($insert) {
            $this->data[$this->primaryKey] = $this->db->lastInsertId();
        }

        $this->afterSave();

        return true;
    }

    /**
     * Delete this record.
     * @throws \RuntimeException
     * @return boolean
     */
    public function delete()
    {
        if (!isset($this->data[$this->primaryKey])) {
            throw new \RuntimeException("Can't delete because item is not loaded.");
        }

        if (!$this->beforeDelete()) {
            return false;
        }

        $st = $this->db->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = ?');
        $st->bindParam(1, $this->data[$this->primaryKey], PDO::PARAM_INT);

        if (!$st->execute()) {
            throw new \RuntimeException("Error while trying to delete item {$this->data[$this->primaryKey]}");
        }

        $this->afterDelete();

        return true;
    }
}
