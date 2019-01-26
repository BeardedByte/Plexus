<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 18:57
 */

namespace Plexus;


use Klein\Klein;
use Twig\Loader\FilesystemLoader;

class Container
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Klein
     */
    private $router;

    /**
     * @var \Twig_Loader_Filesystem
     */
    private $renderer_loader;

    /**
     * @var \Twig_Environment
     */
    private $renderer;

    /**
     * @var array
     */
    private $databases;

    /**
     * @var array
     */
    private $modelmanagers;

    /**
     * @var array
     */
    private $modules;

    /**
     * Container constructor.
     */
    public function __construct(Application $application) {
        $this->application = $application;
        $this->router = new Klein();

        $templates_path = Application::$ROOT_PATH.'templates';
        if (!is_dir($templates_path)) {
            if (!mkdir($templates_path)) {
                throw new \Exception('Impossible de créer le répertoire "'.$templates_path.'"');
            }
        }

        $this->renderer_loader = new \Twig_Loader_Filesystem(Application::$ROOT_PATH.'templates');
        $this->renderer = new \Twig_Environment($this->renderer_loader);
        $this->databases = [];
        $this->controlers = [];
        $this->modelmanagers = [];
        $this->modules = [];
    }

    /**
     * @return Application
     */
    public function getApplication() {
        return $this->application;
    }

    /**
     * @return Klein
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * @return \Twig_Loader_Filesystem
     */
    public function getRendererLoader() {
        return $this->renderer_loader;
    }

    /**
     * @return \Twig_Environment
     */
    public function getRenderer() {
        return $this->renderer;
    }

    /**
     * @param string $name
     * @param \PDO $database
     * @return Container
     * @throws \Exception
     */
    public function addDatabase($name, \PDO $database) {
        if (isset($this->databases[$name])) {
            throw new \Exception('Une base de données est déjà enregistrée sous le nom "'.$name.'".');
        }
        $this->databases[$name] = $database;

        return $this;
    }

    /**
     * @param string $name
     * @return \PDO
     * @throws \Exception
     */
    public function getDatabase($name) {
        if (!isset($this->databases[$name])) {
            throw new \Exception('Aucune base de données nommée "'.$name.'" n\'a été trouvée.');
        }
        return $this->databases[$name];
    }

    /**
     * @return array
     */
    public function getDatabases() {
        return $this->databases;
    }

    /**
     * @param ModelManager $modelmanager
     * @return Container
     * @throws \Exception
     */
    public function addModelManager(ModelManager $modelmanager) {
        if (isset($this->modelmanagers[$modelmanager->getName()])) {
            throw new \Exception('Un modèle est déjà enregistré sous le nom "'.$modelmanager->getName().'".');
        }
        $this->modelmanagers[$modelmanager->getName()] = $modelmanager;

        return $this;
    }

    /**
     * @param string $name
     * @return ModelManager
     * @throws \Exception
     */
    public function getModelManager($name) {
        if (!isset($this->modelmanagers[$name])) {
            throw new \Exception('Aucun modèle nommé "'.$name.'" n\'a été trouvé.');
        }
        return $this->modelmanagers[$name];
    }

    /**
     * @return array
     */
    public function getModelManagers() {
        return $this->modelmanagers;
    }

    /**
     * @param Module $module
     * @return Container
     * @throws \Exception
     */
    public function addModule(Module $module) {
        if (isset($this->modules[$module->getName()])) {
            throw new \Exception('Un module est déjà enregistré sous le nom "'.$module->getName().'".');
        }
        $this->modules[$module->getName()] = $module;

        return $this;
    }

    /**
     * @param string $name
     * @return Module
     * @throws \Exception
     */
    public function getModule($name) {
        if (!isset($this->modules[$name])) {
            throw new \Exception('Aucun module nommé "'.$name.'" n\'a été trouvé.');
        }
        return $this->modules[$name];
    }

    /**
     * @return array
     */
    public function getModules() {
        return $this->modules;
    }

}