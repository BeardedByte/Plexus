<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 18:54
 */

namespace Plexus;


use Plexus\Configuration\EnvironmentConfiguration;
use Plexus\Configuration\RoutesConfiguration;

class Application
{

    /**
     * @var string
     */
    static public $ROOT_PATH  = __DIR__ . '/../../../../';

    /**
     * @var array
     */
    protected $configurations = [];

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var bool
     */
    protected $ready = false;

    /**
     * Application constructor.
     */
    public function __construct() {
        try {
            $this->container = new Container($this);
            $this->configurations['environment'] = new EnvironmentConfiguration();
            $this->configurations['routes'] = new RoutesConfiguration();
            $this->registerModules();
            $this->registerRoutes();
            $this->ready = true;
        } catch (\Exception $e) {
            $this->onError($e);
        }
    }

    /**
     *
     */
    public function run() {
        if ($this->ready) {
            $this->container->getRouter()->onHttpError([$this, 'onHttpError']);
            $this->container->getRouter()->onError([$this, 'onError']);
            $this->container->getRouter()->dispatch();
        }
    }

    public function getContainer() {
        return $this->container;
    }

    /**
     * @return RoutesConfiguration
     */
    public function getRoutesConfiguration() {
        return $this->configurations['routes'];
    }

    /**
     * @return EnvironmentConfiguration
     */
    public function getEnvironmentConfiguration() {
        return $this->configurations['environment'];
    }

    /**
     * @param $identifier
     * @return array
     * @throws \Exception
     */
    public function parseActionIdentifier($identifier) {
        $components = explode(':', $identifier);
        if (count($components) != 3) {
            throw new \Exception('Le format de l\'identifiant d\'action "'.$identifier.'" est invalide');
        }
        return [
            'module' => $components[0],
            'controler' => $components[1],
            'action' => $components[2]
        ];
    }

    /**
     * @throws \Exception
     */
    protected function registerRoutes() {
        $routes = $this->getRoutesConfiguration()->getRoutes();
        foreach ($routes as $route) {
            $components = $this->parseActionIdentifier($route['action']);
            $module = $this->container->getModule($components['module']);
            $controler = $module->getControler($components['controler']);
            if (!method_exists($controler, $components['action'])) {
                throw new \Exception('Aucune action nommée "'.$components['action'].'" n\'existe dans le contrôleur "'.$components['module'].':'.$components['controler'].'"');
            }
            if ($route['method'] !== null) {
                $this->container->getRouter()->respond($route['method'], $route['path'], [$controler, $components['action']]);
            } else if ($route['path'] !== null) {
                $this->container->getRouter()->respond($route['method'], $route['path'], [$controler, $components['action']]);
            } else {
                $this->container->getRouter()->respond('*', [$controler, $components['action']]);
            }
        }
    }

    /**
     *
     */
    public function registerModules() {

    }

    /**
     * @param \Exception $e
     */
    public function onError(\Exception $e) {
        // Logging the error
        Logger::logException('php', $e);
        if ($this->getEnvironmentConfiguration()->isDev()) {
            echo $e->getTraceAsString();
        } else {
            try {
                $this->onHttpError(500);
            } catch (\Exception $e) {
                echo "<body style='background-color: #ecf0f1'><h1 style='color: #2c3e50; text-align: center; margin-top: 5vh; font-family: sans-serif; max-width: 450px; margin-left: auto; margin-right: auto;'>Une erreur a eu lieu lors du chargement de la page...</h1></body>";
            }
        }
    }

    /**
     * @param int $code
     */
    public function onHttpError($code) {
        echo "<body style='background-color: #ecf0f1'><h1 style='color: #2c3e50; text-align: center; margin-top: 5vh; font-family: sans-serif; max-width: 450px; margin-left: auto; margin-right: auto;'>HTTP Error : $code</h1></body>";
    }
}