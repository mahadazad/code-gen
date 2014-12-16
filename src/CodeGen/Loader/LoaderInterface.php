<?php

namespace CodeGen\Loader;

interface LoaderInterface
{
    /**
     * @return CodeGen\Service\ClassBuilder|CodeGen\Service\ClassBuilder[]
     */
    public function load();
}