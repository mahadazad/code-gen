<?php

namespace CodeGen\Loader;

use CodeGen\Service\ClassBuilder;

class JsonLoader extends ArrayLoader
{

    /**
     * @param string json string or json file
     */
    public function __construct($json)
    {
        if (strtolower(substr($json, -5)) === '.json') {
            $json = file_get_contents($json);
        }

        $config = json_decode($json, true);

        $this->config = $config;
    }

}