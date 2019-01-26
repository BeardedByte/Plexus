<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 19:20
 */

namespace Plexus;


class Controler
{
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var string
     */
    protected $name;

    /**
     * Controler constructor.
     * @param $name
     * @param Module $module
     */
    public function __construct($name, Module $module) {
        $this->name = $name;
        $this->module = $module;
    }

    /**
     * @return Module
     */
    public function getModule() {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return Container
     */
    public function getContainer() {
        return $this->module->getContainer();
    }

    /**
     * @param $template
     * @param array $data
     */
    public function render($template, $data=array()) {
        try {
            echo $this->getContainer()->getRenderer()->render($template, $data);
        } catch (\Exception $e) {
            $this->getContainer()->getApplication()->onException($e);
        }
    }
}