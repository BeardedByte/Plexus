<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 23/01/2019
 * Time: 20:36
 */

namespace Plexus;


class ConfigurationParser
{
    protected $config_dirpath;

    protected $config_filepath;

    protected $config_content;

    public function __construct($config_name) {
        $this->config_dirpath = Application::$ROOT_PATH.'config';

        if (!is_dir($this->config_dirpath)) {
            if (!mkdir($this->config_dirpath)) {
                throw new \Exception('Impossible de créer le répertoire "'.$this->config_dirpath.'"');
            }
        }

        $this->config_filepath = $this->config_dirpath.'/'.$config_name.'.yaml';
        if (!file_exists($this->config_filepath)) {
            if (!touch($this->config_filepath)) {
                throw new \Exception('Impossible de créer le fichier "'.$this->config_filepath.'"');
            }
        }

        try {
            $this->config_content = \Symfony\Component\Yaml\Yaml::parseFile($this->config_filepath);
        } catch (\Exception $e) {
            throw new \Exception('Impossible de lire correctement le contenu du fichier de configuration "'.$this->config_filepath.'"');
        }

    }

    /**
     * @param null $components
     * @return mixed|null
     */
    public function get($components=null) {
        if ($components === null) {
            return $this->config_content;
        }

        if (!is_array($components)) {
            $components = [$components];
        }

        $array = $this->config_content;
        foreach ($components as $i => $component) {
            if (isset($array[$component])) {
                if ($i != count($components) - 1) {
                    $array = $array[$component];
                } else {
                    return $array[$component];
                }
            } else {
                return null;
            }
        }

        return null;
    }

}