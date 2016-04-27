<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

use \PDO;

/**
 * The Record class represents a single database record.
 *
 * It is used as an abstraction layer so classes don't need to implement their
 * own database functionality.
 */
class Record {
    const PARAM_BOOL           = 5;
    const PARAM_NULL           = 0;
    const PARAM_INT            = 1;
    const PARAM_STR            = 2;
    const PARAM_LOB            = 3;
    const PARAM_STMT           = 4;
    const PARAM_INPUT_OUTPUT   = -2147483648;
    const PARAM_EVT_ALLOC      = 0;
    const PARAM_EVT_FREE       = 1;
    const PARAM_EVT_EXEC_PRE   = 2;
    const PARAM_EVT_EXEC_POST  = 3;
    const PARAM_EVT_FETCH_PRE  = 4;
    const PARAM_EVT_FETCH_POST = 5;
    const PARAM_EVT_NORMALIZE  = 6;

    const FETCH_LAZY       = 1;
    const FETCH_ASSOC      = 2;
    const FETCH_NUM        = 3;
    const FETCH_BOTH       = 4;
    const FETCH_OBJ        = 5;
    const FETCH_BOUND      = 6;
    const FETCH_COLUMN     = 7;
    const FETCH_CLASS      = 8;
    const FETCH_INTO       = 9;
    const FETCH_FUNC       = 10;
    const FETCH_GROUP      = 65536;
    const FETCH_UNIQUE     = 196608;
    const FETCH_CLASSTYPE  = 262144;
    const FETCH_SERIALIZE  = 524288;
    const FETCH_PROPS_LATE = 1048576;
    const FETCH_NAMED      = 11;

    const ATTR_AUTOCOMMIT          = 0;
    const ATTR_PREFETCH            = 1;
    const ATTR_TIMEOUT             = 2;
    const ATTR_ERRMODE             = 3;
    const ATTR_SERVER_VERSION      = 4;
    const ATTR_CLIENT_VERSION      = 5;
    const ATTR_SERVER_INFO         = 6;
    const ATTR_CONNECTION_STATUS   = 7;
    const ATTR_CASE                = 8;
    const ATTR_CURSOR_NAME         = 9;
    const ATTR_CURSOR              = 10;
    const ATTR_ORACLE_NULLS        = 11;
    const ATTR_PERSISTENT          = 12;
    const ATTR_STATEMENT_CLASS     = 13;
    const ATTR_FETCH_TABLE_NAMES   = 14;
    const ATTR_FETCH_CATALOG_NAMES = 15;
    const ATTR_DRIVER_NAME         = 16;
    const ATTR_STRINGIFY_FETCHES   = 17;
    const ATTR_MAX_COLUMN_LEN      = 18;
    const ATTR_EMULATE_PREPARES    = 20;
    const ATTR_DEFAULT_FETCH_MODE  = 19;

    const ERRMODE_SILENT                = 0;
    const ERRMODE_WARNING               = 1;
    const ERRMODE_EXCEPTION             = 2;
    const CASE_NATURAL                  = 0;
    const CASE_LOWER                    = 2;
    const CASE_UPPER                    = 1;
    const NULL_NATURAL                  = 0;
    const NULL_EMPTY_STRING             = 1;
    const NULL_TO_STRING                = 2;
    const ERR_NONE                      = '00000';
    const FETCH_ORI_NEXT                = 0;
    const FETCH_ORI_PRIOR               = 1;
    const FETCH_ORI_FIRST               = 2;
    const FETCH_ORI_LAST                = 3;
    const FETCH_ORI_ABS                 = 4;
    const FETCH_ORI_REL                 = 5;
    const CURSOR_FWDONLY                = 0;
    const CURSOR_SCROLL                 = 1;
    const MYSQL_ATTR_USE_BUFFERED_QUERY = 1000;
    const MYSQL_ATTR_LOCAL_INFILE       = 1001;
    const MYSQL_ATTR_INIT_COMMAND       = 1002;
    const MYSQL_ATTR_READ_DEFAULT_FILE  = 1003;
    const MYSQL_ATTR_READ_DEFAULT_GROUP = 1004;
    const MYSQL_ATTR_MAX_BUFFER_SIZE    = 1005;
    const MYSQL_ATTR_DIRECT_QUERY       = 1006;

    public static $__CONN__    = false;
    public static $__QUERIES__ = [];

    /**
     * Sets a static reference for the connection to the database.
     *
     * @param <type> $connection
     */
    final public static function connection($connection) {
        self::$__CONN__ = $connection;
    }

    /**
     * Returns a reference to a database connection.
     *
     * @return <type>
     */
    final public static function getConnection() {
        return self::$__CONN__;
    }

    /**
     * Logs an SQL query.
     *
     * @param string $sql SQL query string.
     */
    final public static function logQuery($sql) {
        self::$__QUERIES__[] = $sql;
    }

    /**
     * Retrieves all logged queries.
     *
     * @return array An array of queries.
     */
    final public static function getQueryLog() {
        return self::$__QUERIES__;
    }

    /**
     * Returns the number of logged queries.
     *
     * @return int Number of logged queries.
     */
    final public static function getQueryCount() {
        return count(self::$__QUERIES__);
    }

    /**
     * Executes an SQL query.
     *
     * @param string $sql   SQL query to execute.
     * @param array $values Values belonging to the SQL query if its a prepared statement.
     * @return <type>       An array of objects, PDOStatement object or FALSE on failure.
     */
    final public static function query($sql, $values=false) {
        self::logQuery($sql);

        if (is_array($values)) {
            $stmt = self::$__CONN__->prepare($sql);
            $stmt->execute($values);
            return $stmt->fetchAll(self::FETCH_OBJ);
        }

        return self::$__CONN__->query($sql);
    }

    /**
     * Returns a database table name.
     *
     * The name that is returned is based on the classname or on the TABLE_NAME
     * constant in that class if that constant exists.
     *
     * @param string $class_name
     * @return string Database table name.
     */
    final public static function tableNameFromClassName($class_name) {
        try {
            if (class_exists($class_name) && defined($class_name . '::TABLE_NAME'))
                return TABLE_PREFIX . constant($class_name . '::TABLE_NAME');
        }
        catch (Exception $e) {
            return TABLE_PREFIX . Inflector::underscore($class_name);
        }
    }

    /**
     * Escapes quotes in a query string.
     *
     * @param string $value The query string to escape.
     * @return string       The escaped string.
     */
    final public static function escape($value) {
        return self::$__CONN__->quote($value);
    }

    /**
     * Retrieves the autogenerated primary key for the last inserted record.
     *
     * @return string A key.
     */
    final public static function lastInsertId() {
        // PostgreSQL does not support lastInsertId retrieval without knowing the sequence name
        if (self::$__CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql = 'SELECT lastval();';

            if ($result = self::$__CONN__->query($sql)) {
                return $result->fetchColumn();
            }

            return 0;
        }

        return self::$__CONN__->lastInsertId();
    }

    /**
     * Constructor for the Record class.
     *
     * If the $data parameter is given and is an array, the constructor sets
     * the class's variables based on the key=>value pairs found in the array.
     *
     * @param array $data An array of key,value pairs.
     */
    public function __construct($data=false) {
        if (is_array($data)) {
            $this->setFromData($data);
        }
    }

    /**
     * Sets the class's variables based on the key=>value pairs in the given array.
     *
     * @param array $data An array of key,value pairs.
     */
    public function setFromData($data) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Generates an insert or update string from the supplied data and executes it
     *
     * @return boolean True when the insert or update succeeded.
     */
    public function save() {
        if (! $this->beforeSave()) return false;

        $value_of = [];

        if (empty($this->id)) {

            if (! $this->beforeInsert()) return false;

            $columns = $this->getColumns();

            // Escape and format for SQL insert query
            // @todo check if we like this new method of escaping and defaulting
            foreach ($columns as $column) {
                // Make sure we don't try to add "id" field;
                if ($column === 'id') {
                    continue;
                }

                if (!empty($this->$column) || is_numeric($this->$column)) { // Do include 0 as value
                    $value_of[$column] = self::$__CONN__->quote($this->$column);
                }
                elseif (isset($this->$column)) { // Properly fallback to the default column value instead of relying on an empty string
                    // SQLite can't handle the DEFAULT value
                    if (self::$__CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) != 'sqlite') {
                        $value_of[$column] = 'DEFAULT';
                    }
                }
            }

            $sql = 'INSERT INTO ' . self::tableNameFromClassName(get_class($this)) . ' ('
                . implode(', ', array_keys($value_of)) . ') VALUES (' . implode(', ', array_values($value_of)) . ')';
            $return   = self::$__CONN__->exec($sql) !== false;
            $this->id = self::lastInsertId();

            if (! $this->afterInsert()) return false;

        } else {

            if (! $this->beforeUpdate()) return false;

            $columns = $this->getColumns();

            // Escape and format for SQL update query
            foreach ($columns as $column) {
                if (!empty($this->$column) || is_numeric($this->$column)) { // Do include 0 as value
                    $value_of[$column] = $column . '=' . self::$__CONN__->quote($this->$column);
                }
                elseif (isset($this->$column)) { // Properly fallback to the default column value instead of relying on an empty string
                    // SQLite can't handle the DEFAULT value
                    if (self::$__CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) != 'sqlite') {
                        $value_of[$column] = $column . '=DEFAULT';
                    }
                    else{
                        //Since DEFAULT values don't work in SQLite empty strings should be passed explicitly
                        $value_of[$column] = $column . "=''";
                    }
                }
            }

            unset($value_of['id']);

            $sql = 'UPDATE ' . self::tableNameFromClassName(get_class($this)) . ' SET '
                . implode(', ', $value_of) . ' WHERE id = ' . $this->id;
            $return = self::$__CONN__->exec($sql) !== false;

            if (! $this->afterUpdate()) return false;
        }

        self::logQuery($sql);

        if (! $this->afterSave()) return false;

        return $return;
    }

    /**
     * Generates a delete string and executes it.
     *
     * @param string $table The table name.
     * @param string $where The query condition.
     * @return boolean      True if delete was successful.
     */
    public function delete() {
        if (! $this->beforeDelete()) return false;
        $sql = 'DELETE FROM ' . self::tableNameFromClassName(get_class($this))
            . ' WHERE id=' . self::$__CONN__->quote($this->id);

        // Run it !!...
        $return = self::$__CONN__->exec($sql) !== false;
        if (! $this->afterDelete()) {
            $this->save();
            return false;
        }

        self::logQuery($sql);

        return $return;
    }

    /**
     * Allows sub-classes do stuff before a Record is saved.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeSave() { return true; }

    /**
     * Allows sub-classes do stuff before a Record is inserted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeInsert() { return true; }

    /**
     * Allows sub-classes do stuff before a Record is updated.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeUpdate() { return true; }

    /**
     * Allows sub-classes do stuff before a Record is deleted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeDelete() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is saved.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterSave() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is inserted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterInsert() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is updated.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterUpdate() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is deleted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterDelete() { return true; }

    /**
     * Returns an array of all columns in the table.
     *
     * It is a good idea to rewrite this method in all your model classes.
     * This function is used in save() for creating the insert and/or update
     * sql query.
     */
    public function getColumns() {
        return array_keys(get_object_vars($this));
    }

    /**
     * Inserts a record into the database.
     *
     * @param string $class_name    The classname of the record that should be inserted.
     * @param array $data           An array of key/value pairs to be inserted.
     * @return boolean              Returns true when successful.
     */
    public static function insert($class_name, $data) {
        $keys   = [];
        $values = [];

        foreach ($data as $key => $value) {
            $keys[]   = $key;
            $values[] = self::$__CONN__->quote($value);
        }

        $sql = 'INSERT INTO ' . self::tableNameFromClassName($class_name) . ' (' . join(', ', $keys) . ') VALUES (' . join(', ', $values) . ')';

        self::logQuery($sql);

        // Run it !!...
        return self::$__CONN__->exec($sql) !== false;
    }

    /**
     * Updates an existing record in the database.
     *
     * @param string $class_name    The classname of the record to be updated.
     * @param array $data           An array of key/value pairs to be updated.
     * @param string $where         An SQL WHERE clause to specify a specific record.
     * @param array $values         An array of values if this is a prepared statement.
     * @return <type>
     */
    public static function update($class_name, $data, $where, $values=[]) {
        $setters = [];

        // Prepare request by binding keys
        foreach ($data as $key => $value) {
            $setters[] = $key . '=' . self::$__CONN__->quote($value);
        }

        $sql = 'UPDATE ' . self::tableNameFromClassName($class_name) . ' SET ' . join(', ', $setters) . ' WHERE ' . $where;

        self::logQuery($sql);

        $stmt = self::$__CONN__->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Deletes a specified records from the database.
     *
     * @param string $class_name    The classname for the record to be deleted.
     * @param string $where         An SQL WHERE clause to specify a specific record.
     * @param array $values         An array of values if this is a prepared statement.
     * @return boolean              True when the delete was successful.
     */
    public static function deleteWhere($class_name, $where, $values=[]) {
        $sql = 'DELETE FROM ' . self::tableNameFromClassName($class_name) . ' WHERE ' . $where;

        self::logQuery($sql);

        $stmt = self::$__CONN__->prepare($sql);
        return $stmt->execute($values);
    }


    /**
     * Returns true if a record exists in the database.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to specify a subset if desired.
     * @param array $values         An array of values if this is a prepared statement.
     * @return boolean              TRUE if record exists, FALSE if it doesn't.
     */
    public static function existsIn($class_name, $where=false, $values=[]) {
        $sql = 'SELECT EXISTS(SELECT 1 FROM ' . self::tableNameFromClassName($class_name) . ($where ? ' WHERE ' . $where:'') . ' LIMIT 1)';

        $stmt = self::$__CONN__->prepare($sql);
        $stmt->execute($values);

        self::logQuery($sql);

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Returns a single class instance or an array of instances.
     *
     * This method guarantees sane defaults, making the `options` argument
     * optional. It is important to note however, that when using prepared
     * statements with placeholders in for example the `WHERE` clause, the
     * `values` option is mandatory.
     *
     * Valid options are: 'select', 'where', 'group_by', 'having', 'order_by', 'limit', 'offset', 'values'
     *
     * Example usage:
     * <code>
     * // Note that MyClass extends Record
     * $object = MyClass::find(array(
     *     'select'     => 'column1, column2',
     *     'where'      => 'id = ? and slug = ?',
     *     'group_by'   => 'column2',
     *     'having'     => 'column2 = ?',
     *     'order_by'   => 'column3 ASC',
     *     'limit'      => 10,
     *     'offset'     => 20,
     *     'values'     => array($id, $slug, 'some-value-for-having-clause')
     * ));
     * </code>
     *
     * @param   array   $options    Array of options for the query.
     * @return  mixed               Single object, array of objects or false on failure.
     *
     * @todo    Decide if we'll keep the from and joins options since they clash heavily with the one Record == one DB tuple idea.
     */
    public static function find($options = []) {
        // @todo Replace by InvalidArgumentException if not array based on logger decision.
        $options = (is_null($options)) ? [] : $options;

        $class_name = get_called_class();
        $table_name = self::tableNameFromClassName($class_name);

        // Collect attributes
        $ses    = isset($options['select']) ? trim($options['select'])   : '';
        $frs    = isset($options['from'])   ? trim($options['from'])     : '';
        $jos    = isset($options['joins'])  ? trim($options['joins'])    : '';
        $whs    = isset($options['where'])  ? trim($options['where'])    : '';
        $gbs    = isset($options['group'])  ? trim($options['group'])    : '';
        $has    = isset($options['having']) ? trim($options['having'])   : '';
        $obs    = isset($options['order'])  ? trim($options['order'])    : '';
        $lis    = isset($options['limit'])  ? (int) $options['limit']    : 0;
        $ofs    = isset($options['offset']) ? (int) $options['offset']   : 0;
        $values = isset($options['values']) ? (array) $options['values'] : [];

        // Asked for single Record?
        $single = ($lis === 1) ? true : false;

        // Prepare query parts
        $select      = empty($ses) ? 'SELECT *'         : "SELECT $ses";
        $from        = empty($frs) ? "FROM $table_name" : "FROM $frs";
        $joins       = empty($jos) ? ''                 : $jos;
        $where       = empty($whs) ? ''                 : "WHERE $whs";
        $group_by    = empty($gbs) ? ''                 : "GROUP BY $gbs";
        $having      = empty($has) ? ''                 : "HAVING $has";
        $order_by    = empty($obs) ? ''                 : "ORDER BY $obs";
        $limit       = $lis > 0    ? "LIMIT $lis"       : '';
        $offset      = $ofs > 0    ? "OFFSET $ofs"      : '';

        // Build the query
        $sql = "$select $from $joins $where $group_by $having $order_by $limit $offset";

        // Run query
        $objects = self::findBySql($sql, $values);

        return ($single) ? (!empty($objects) ? $objects[0] : false) : $objects;
    }

    private static function findBySql($sql, $values = null) {
        $class_name = get_called_class();

        self::logQuery($sql);

        // Prepare and execute
        $stmt = self::getConnection()->prepare($sql);
        if (!$stmt->execute($values)) {
            return false;
        }

        $objects = [];
        while ($object = $stmt->fetchObject($class_name)) {
            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Returns a record based on it's id.
     *
     * Default method so that you don't have to create one for every model you write.
     * Can of course be overwritten by a custom findById() method (for instance when you want to include another model)
     *
     * @param int $id       Object's id
     * @return              Single object
     */
    public static function findById($id) {
        return self::findOne([
            'where'  => 'id = :id',
            'values' => [':id' => (int) $id]
        ]);
    }

    //
    // Note: lazy finder or getter method. Pratical when you need something really
    //       simple no join or anything will only generate simple select * from table ...
    //

    /**
     * Returns a single Record class instance from the database based on ID.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $id            The ID of the record to be found.
     * @return Record               A record instance or false on failure.
     */
    public static function findByIdFrom($class_name, $id) {
        return $class_name::findById($id);
    }

    /**
     * Returns a single object, retrieved from the database.
     *
     * @param array $options        Options array containing parameters for the query
     * @return                      Single object
     */
    public static function findOne($options = []) {
        $options['limit'] = 1;
        return self::find($options);
    }

    /**
     * Returns a single Record class instance.
     *
     * The instance is retrieved from the database based on a specified field's
     * value.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to find a specific record.
     * @param array $values         An array of values if this is a prepared statement.
     * @return Record               A record instance or false on failure.
     */
    public static function findOneFrom($class_name, $where, $values=[]) {
        return $class_name::findOne([
            'where'  => $where,
            'values' => $values
        ]);
    }

    /**
     * Returns an array of Record instances.
     *
     * Retrieves all records, or a subset thereof if the $where parameter is
     * used, for a specific database table.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to specify a subset if desired.
     * @param array $values         An array of values if this is a prepared statement.
     * @return array                An array of Records instances.
     */
    public static function findAllFrom($class_name, $where=false, $values=[]) {
        if ($where) {
            return $class_name::find([
                'where'  => $where,
                'values' => $values
            ]);
        } else {
            return $class_name::find();
        }
    }

    /**
     * Returns the number of records.
     *
     * Returns a total of all records in the specified database table or a count
     * for a specified subset thereof.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to specify a subset if desired.
     * @param array $values         An array of values if this is a prepared statement.
     * @return int                  The number of records in the table or a subset thereof.
     */
    public static function countFrom($class_name, $where=false, $values=[]) {
        $sql = 'SELECT COUNT(*) AS nb_rows FROM ' . self::tableNameFromClassName($class_name) . ($where ? ' WHERE ' . $where:'');

        $stmt = self::$__CONN__->prepare($sql);
        $stmt->execute($values);

        self::logQuery($sql);

        return (int) $stmt->fetchColumn();
    }

}
