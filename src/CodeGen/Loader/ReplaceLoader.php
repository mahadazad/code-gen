<?php

namespace CodeGen\Loader;

use CodeGen\Service\ClassBuilder;

class ReplaceLoader implements LoaderInterface
{
    protected $innerLoader;

    /**
     * @param LoaderInterface $loader
     * @param string $search
     * @param string $replace
     */
    public function __construct(LoaderInterface $loader, $search, $replace)
    {
        if (!$loader instanceof ArrayLoader) {
            throw new \InvalidArgumentException('$loader must be instance of CodeGen\Loader\ArrayLoader');
        }

        $config = $loader->getConfig();

        array_walk_recursive($config, function (&$value) use ($search, $replace) {
            $value = str_replace($search, $replace, $value);
        });

        $loader->setConfig($config);

        $this->innerLoader = $loader;
    }

    /**
     * @return CodeGen\Service\ClassBuilder|CodeGen\Service\ClassBuilder[]
     */
    public function load()
    {
        return $this->innerLoader->load();
    }

}