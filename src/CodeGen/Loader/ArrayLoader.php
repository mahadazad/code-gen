<?php

namespace CodeGen\Loader;

use CodeGen\Service\ClassBuilder;
use Zend\Code\Generator\PropertyValueGenerator;

class ArrayLoader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return null|CodeGen\Service\ClassBuilder[]
     */
    public function load()
    {
        $classes = array();

        if (array_key_exists('classes', $this->config) && is_array($this->config['classes'])) {
            foreach ($this->config['classes'] as $classConfig) {
                if (array_key_exists('name', $classConfig)) {
                    $class = new ClassBuilder();
                    $this->setClassName($class, $classConfig)
                         ->setAbstract($class, $classConfig)
                         ->setNamespace($class, $classConfig)
                         ->setUses($class, $classConfig)
                         ->setExtends($class, $classConfig)
                         ->setImplements($class, $classConfig)
                         ->setProperties($class, $classConfig)
                         ->setMethods($class, $classConfig)
                         ->setSavePath($class, $classConfig);

                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setClassName(ClassBuilder $class, array $config)
    {
        $class->setClassName($config['name']);

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setAbstract(ClassBuilder $class, array $config)
    {
        if (array_key_exists('abstract', $config)) {
            $class->setAbstract($config['abstract']);
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setNamespace(ClassBuilder $class, array $config)
    {
        if (array_key_exists('namespace', $config)) {
            $class->setNamespaceName($config['namespace']);
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setUses(ClassBuilder $class, array $config)
    {
        if (array_key_exists('uses', $config) && is_array($config['uses'])) {
            foreach ($config['uses'] as $use) {
                if (array_key_exists('use', $use)) {
                    $alias = array_key_exists('alias', $config) ? $config['alias'] : null;
                    $class->addUse($use['use'], $alias);
                }
            }
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setExtends(ClassBuilder $class, array $config)
    {
        if (array_key_exists('extends', $config)) {
            $class->setExtends($config['extends']);
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setImplements(ClassBuilder $class, array $config)
    {
        if (array_key_exists('implements', $config) && is_array($config['implements'])) {
            $class->setInterfaces($config['implements']);
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setProperties(ClassBuilder $class, array $config)
    {
        if (array_key_exists('properties', $config) && is_array($config['properties'])) {
            foreach ($config['properties'] as $property) {
                if (array_key_exists('name', $property)) {
                    $name = $property['name'];
                    $visibility = array_key_exists('visibility', $property) ? $property['visibility'] : 'public';
                    $static = array_key_exists('static', $property) ? $property['static'] : false;
                    $default_value = array_key_exists('default_value', $property) ? $property['default_value'] : null;
                    $type = array_key_exists('type', $property) ? $property['type'] : PropertyValueGenerator::TYPE_AUTO;
                    $class->addProperty($name, $visibility, $static, $default_value, $type);
                }
            }
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setMethods(ClassBuilder $class, array $config)
    {
        if (array_key_exists('methods', $config) && is_array($config['methods'])) {
            foreach ($config['methods'] as $method) {
                if (array_key_exists('name', $method)) {
                    $name = $method['name'];
                    $visibility = array_key_exists('visibility', $method) ? $method['visibility'] : 'public';
                    $static = array_key_exists('static', $method) ? $method['static'] : false;
                    $final = array_key_exists('final', $method) ? $method['final'] : false;
                    $content = array_key_exists('content', $method) ? $method['content'] : '';
                    $parameters = array();

                    if (array_key_exists('parameters', $method) && is_array($method['parameters'])) {
                        foreach ($parameters as $parameter) {
                            if (array_key_exists('name', $parameter) && array_key_exists('type', $parameter)) {
                                $prop = new \stdClass();
                                $prop->name = $parameter['name'];
                                $prop->type = $parameter['type'];
                                $prop->isByRef = array_key_exists('passed_by_reference', $parameter) ? $parameter['passed_by_reference'] : false;
                                $prop->defaultValue = array_key_exists('default_value', $parameter) ? $parameter['default_value'] : ClassBuilder::NONE;

                                $properties[] = $prop;
                            }
                        }
                    }

                    $class->addMethod($name, $visibility, $static, $final, $parameters, $content);
                }
            }
        }

        return $this;
    }

    /**
     * @param  ClassBuilder $class
     * @param  array        $config
     * @return ArrayLoader
     */
    protected function setSavePath(ClassBuilder $class, array $config)
    {
        if (array_key_exists('path', $config)) {
            $class->setSavePath($config['path']);
        }

        return $this;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
