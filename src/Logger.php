<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 20:38
 */

namespace Plexus;


class Logger
{



    /**
     * @param $identifier
     * @param $data
     */
    static public function log($identifier, $data) {
        $log_dirpath = $log_filepath = Application::$ROOT_PATH.'logs';
        if (!is_dir($log_dirpath)) {
            mkdir($log_dirpath);
        }
        $log_filepath = $log_dirpath.'/'.$identifier.'.log';
        $line = '['.date('d-M-Y H:i:s e').'] ['.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].'] '.strval($data);
        file_put_contents($log_filepath,  $line.PHP_EOL, FILE_APPEND);
    }

    /**
     * @param $identifier
     * @param $message
     */
    static public function logMessage($identifier, $message) {
        Logger::log($identifier, $message);
    }

    /**
     * @param $identifier
     * @param \Exception $e
     */
    static public function logException($identifier, \Exception $e) {
        $data = 'Exception: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
        Logger::log($identifier, $data);
    }
}