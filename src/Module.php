<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 22:12
 */

namespace Plexus;


use Twig\Loader\FilesystemLoader;

class Module
{

    protected $application;

    protected $module_name;

    protected $module_dirpath;

    protected $controlers;

    /**
     * Module constructor.
     * @param $name
     * @param Application $application
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     */
    public function __construct($name, Application $application) {
        $this->application = $application;
        $this->module_name = $name;
        $this->controlers = [];

        // ReflectionClass allow us to get info of the child class
        $classInfo = new \ReflectionClass($this);
        $this->module_dirpath = dirname($classInfo->getFileName());

        $controlers_dirpath = $this->module_dirpath.'/Controler';

        // Getting automatically all the controlers
        if (is_dir($controlers_dirpath)) {
            $controler_files = scandir($controlers_dirpath);
            if ($controler_files) {
                foreach ($controler_files as $controler_file) {
                    if ($controler_file == '.' || $controler_file == '..') {
                        continue;
                    }
                    $controler_name = str_replace('.php', '', $controler_file);
                    $controler_class = '\\'.$classInfo->getNamespaceName().'\\Controler\\'.$controler_name;
                    if (!class_exists($controler_class)) {
                        throw new \Exception('Aucune classe nommée "'.$controler_class.'" n\'a été trouvée.');
                    }
                    $this->addControler(new $controler_class($controler_name,$this));
                }
            }
        }

        $template_dirpath = $this->module_dirpath.'/templates';
        if (is_dir($template_dirpath)) {
            $this->getContainer()->getRendererLoader()->addPath($template_dirpath, $this->getName());
        }
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->module_name;
    }

    /**
     * @return string
     */
    public function getModuleDirPath() {
        return $this->module_dirpath;
    }

    /**
     * @return null|string
     */
    public function getTemplateDirPath() {
        $template_dirpath = $this->module_dirpath.'/Templates';
        if (is_dir($template_dirpath)) {
            return $template_dirpath;
        }
        return null;
    }

    /**
     * @param $name
     * @return Controler
     * @throws \Exception
     */
    public function getControler($name) {
        if (!isset($this->controlers[$name])) {
            throw new \Exception('Aucun contrôleur nommé "'.$name.'" n\'a été trouvé dans le module "'.$this->module_name.'".');
        }
        return $this->controlers[$name];
    }

    /**
     * @param Controler $controler
     * @return Module
     * @throws \Exception
     */
    public function addControler(Controler $controler) {
        if (isset($this->controlers[$controler->getName()])) {
            throw new \Exception('Un contrôleur est déjà enregistré sous le nom "'.$controler->getName().'" '.'dans le module "'.$this->module_name.'".');
        }
        $this->controlers[$controler->getName()] = $controler;

        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer() {
        return $this->application->getContainer();
    }
}