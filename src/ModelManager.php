<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 19:20
 */

namespace Plexus;


class ModelManager
{
    /**
     * @var \PDO
     */
    public $database;

    /**
     * @var string
     */
    public $tableName = "";

    /**
     * @var array
     */
    public $structure = [];

    /**
     * @var bool
     */
    public $useDefaultId = true;

    /**
     * ModelManager constructor.
     *
     * @param \PDO $database
     * @param $tableName
     * @param bool $useDefaultId
     */
    public function __construct(\PDO $database, $tableName, $useDefaultId=true) {
        $this->database = $database;
        $this->tableName = $tableName;
        $this->useDefaultId = $useDefaultId;
        $this->get_structure();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->tableName;
    }

    /** Utils */

    /**
     * Return the structure of the table
     */
    protected function get_structure() {
        $output = $this->database->query("PRAGMA table_info($this->tableName)");
        $this->structure = $output->fetchAll(\PDO::FETCH_ASSOC);
    }


    /** Query Builder */

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder() {
        return new QueryBuilder($this->tableName);
    }

    /**
     * @param QueryBuilder $qb
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function executeQueryBuilder(QueryBuilder $qb, $data=[]) {
        return $this->execute($qb->query(), $data);
    }

    /**
     * @param $sql
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function execute($sql, $data=[]) {
        $request = $this->database->prepare($sql);
        if (!$request->execute($data)) {
            throw new \Exception('Une erreur est survenue');
        };
        return $request->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Requests builder */

    /**
     * @return string
     */
    protected function build_insert_request() {
        $sql = "INSERT INTO $this->tableName VALUES(";
        $acc = 0;
        foreach ($this->structure as $column) {
            $acc += 1;
            if ($acc > 1) {
                $sql .= ",";
            }
            $sql .= ":".$column['name'];

        }
        $sql .= ")";

        return $sql;
    }

    /**
     * @param $model
     *
     * @return string
     */
    protected function build_select_request($model) {
        $sql = "SELECT * FROM $this->tableName";
        if (count($model) > 0) {
            $sql .= " WHERE ";
            $acc = 0;
            foreach ($this->structure as $column) {
                if (array_key_exists($column['name'], $model)) {
                    $acc += 1;
                    if ($acc > 1) {
                        $sql .= " AND ";
                    }
                    $sql .= $column['name']." = :".$column['name'];
                }
            }
        }
        return $sql;
    }

    /**
     * @return string
     */
    protected function build_update_request() {
        $sql = "UPDATE $this->tableName SET ";
        $acc = 0;
        foreach ($this->structure as $column) {
            if ($column['name'] != 'id') {
                $acc += 1;
                if ($acc > 1) {
                    $sql .= ",";
                }
                $sql .= $column['name']." = :".$column['name'];
            }
        }
        $sql .= " WHERE id = :id";
        return $sql;
    }


    /** Basic requests */

    /**
     * @param $model
     * @param $replacements
     *
     * @throws \Exception
     */
    public function insert(&$model, $replacements=[]) {
        $sql = $this->build_insert_request();

        $updateId = false;

        if ($this->useDefaultId) {
            if (isset($model['id'])) {
                unset($model['id']);
                $updateId = true;
            }
        }

        $_model = [];
        foreach ($model as $key => $value) {
            if (array_key_exists($key, $replacements)) {
                $sql = str_replace(':'.$key, $replacements[$key], $sql);
            } else {
                $_model[$key] = $value;
            }
        }

        $request = $this->database->prepare($sql);
        if (!$request->execute($_model)) {
            throw new \Exception('Une erreur est survenue');
        };

        if ($updateId) {
            $model['id'] = $this->database->lastInsertId($this->tableName);
        }
    }

    /**
     * @param $model
     * Model is an array with only the fields you want the search to be based on
     * e.g. $array('id' => 1)
     *
     * @return array
     * @throws \Exception
     */
    public function select($model) {
        $sql = $this->build_select_request($model);
        $request = $this->database->prepare($sql);
        if (!$request->execute($model)) {
            throw new \Exception('Une erreur est survenue');
        };

        return $request->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $model
     * @param $replacements
     * The update selection is based on the id
     *
     * @throws \Exception
     */
    public function update($model, $replacements=[]) {
        $sql = $this->build_update_request();

        $_model = [];
        foreach ($model as $key => $value) {
            if (isset($replacements[$key])) {
                $sql = str_replace(':'.$key, $replacements[$key], $sql);
            } else {
                $_model[$key] = $value;
            }
        }

        $request = $this->database->prepare($sql);
        if (!$request->execute($_model)) {
            throw new \Exception('Une erreur est survenue');
        };
    }

    /**
     * @param $model
     * The delete selection is based on the id
     *
     * @throws \Exception
     */
    public function delete($model) {

        $_model = ['id' => $model['id']];

        $sql = "DELETE FROM $this->tableName WHERE id = :id";

        $request = $this->database->prepare($sql);
        if (!$request->execute($_model)) {
            throw new \Exception('Une erreur est survenue');
        };
    }


    /** Get From DB */

    /**
     * @return array
     * @throws \Exception
     */
    public function get_all() {
        return $this->select(array());
    }

    /**
     * @param $id
     *
     * @return array
     * @throws \Exception
     */
    public function get($id) {
        $users = $this->select(array('id' => $id));

        if (count($users) == 1) {
            return $users[0];
        }

        return null;
    }


    /** Local function */

    /**
     * Create an empty model
     * @return array
     */
    public function create() {
        $model = [];
        foreach ($this->structure as $column) {
            switch ($column['type']) {
                case 'TEXT':
                    $model[$column['name']] = '';
                    break;
                case 'INTEGER':
                    $model[$column['name']] = 0;
                    break;
                case 'REAL':
                    $model[$column['name']] = 0.0;
                    break;
                default:
                    $model[$column['name']] = '';
                    break;
            }
        }

        return $model;
    }

    /**
     * @param $array
     * @param null $fields
     * Create a model with the data from an array
     * You can limit the fields filled from the array
     *
     * @return array
     */
    public function build_from($array, $fields = null) {

        $model = $this->create();

        foreach ($this->structure as $column) {

            if (is_array($fields) && !in_array($column['name'], $fields)) {
                continue;
            }

            if (isset($array[$column['name']])) {
                $value = $array[$column['name']];
                switch ($column['type']) {
                    case 'TEXT':
                        $value = strval($value);
                        break;
                    case 'INTEGER':
                        $value = intval($value);
                        break;
                    case 'REAL':
                        $value = doubleval($value);
                        break;
                    default:
                        $value = strval($value);
                        break;
                }
                $model[$column['name']] = $value;
            }
        }
        return $model;
    }

    /**
     * @param $model
     * @param $array
     * @param null $fields
     * Update a model with the data from an array
     * You can limit the fields filled from the array
     *
     * @return mixed
     */
    public function update_from($model, $array, $fields = null) {

        foreach ($this->structure as $column) {

            if (is_array($fields) && !in_array($column['name'], $fields)) {
                continue;
            }

            if (isset($array[$column['name']])) {
                $value = $array[$column['name']];
                switch ($column['type']) {
                    case 'TEXT':
                        $value = strval($value);
                        break;
                    case 'INTEGER':
                        $value = intval($value);
                        break;
                    case 'REAL':
                        $value = doubleval($value);
                        break;
                    default:
                        $value = strval($value);
                        break;
                }
                $model[$column['name']] = $value;
            }
        }
        return $model;
    }

    /**
     * @param $model
     *
     * @return bool
     */
    public function validate($model) {
        return true;
    }
}