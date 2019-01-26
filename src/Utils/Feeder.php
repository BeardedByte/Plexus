<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 21:13
 */

namespace Plexus\Utils;


class Feeder
{
    /**
     * @param $keys
     * @param $data
     * @return array
     */
    static public function feed($keys, $data) {
        $output = [];
        foreach ($keys as $key) {
            $output[$key] = null;
            if (isset($data[$key])) {
                $output[$key] = $data[$key];
            }
        }

        return $output;
    }
}