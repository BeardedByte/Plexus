<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 10/02/2019
 * Time: 01:33
 */

namespace Plexus\Utils;


class Console
{
    static public function log($data) {
        echo "<script>console.log(".json_encode($data).")</script>";
    }
}