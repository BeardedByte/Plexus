<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 19:20
 */

namespace Plexus;


class ModelManager_MySQL extends ModelManager {
    protected function get_structure() {
        $output = $this->database->query("DESCRIBE $this->tableName");
        $structure = $output->fetchAll(\PDO::FETCH_ASSOC);
        $this->structure = [];
        foreach ($structure as $column) {
            $this->structure[] = [
                'name' => $column['Field'],
                'type' => $column['Type']
            ];
        }
    }

    /**
     * @param $model
     * @param array $replacements
     * @throws \Exception
     */
    public function insert(&$model, $replacements=[]) {
        $sql = $this->build_insert_request();

        $updateId = true;

        $_model = [];
        foreach ($model as $key => $value) {
            if (array_key_exists($key, $replacements)) {
                $sql = str_replace(':'.$key.',', $replacements[$key].',', $sql);
                $sql = str_replace(':'.$key.')', $replacements[$key].')', $sql);
            } else {
                $_model[$key] = $value;
            }
        }

        $request = $this->database->prepare($sql);

        if (!$request->execute($_model)) {
            throw new \Exception('Une erreur est survenue [SQL: '.$sql.']');
        };

        if ($updateId) {
            $model['id'] = $this->database->lastInsertId();
        }
    }
}