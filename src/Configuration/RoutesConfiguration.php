<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 21:10
 */

namespace Plexus\Configuration;


use Plexus\ConfigurationParser;
use Plexus\Utils\Feeder;


class RoutesConfiguration extends ConfigurationParser {

    /**
     * RoutesConfiguration constructor.
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('routes');
    }

    /**
     * @return array
     */
    public function getRoutes() {
        $_routes = $this->get();
        if ($_routes === null) {
            return [];
        }

        $routes = [];
        foreach ($_routes as $_name => $_route) {
            $routes[] = Feeder::feed(['method' ,'path', 'action'], $_route);
        }

        return $routes;
    }
}