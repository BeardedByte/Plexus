<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 21:10
 */

namespace Plexus\Configuration;


use Plexus\ConfigurationParser;

class EnvironmentConfiguration extends ConfigurationParser {

    /**
     * EnvironmentConfiguration constructor.
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('environment');
    }

    /**
     * @return bool
     */
    public function isDev() {
        $env = $this->get('env');

        if ($env === null) {
            return false;
        }

        if (strtolower($env) == 'dev') {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isProd() {
        $env = $this->get('env');

        if ($env === null) {
            return true;
        }

        if (strtolower($env) != 'prod') {
            return false;
        }
        return true;
    }




}