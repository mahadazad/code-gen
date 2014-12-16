<?php

namespace CodeGen\Service;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Filter\Word\UnderscoreToCamelCase;

class ClassBuilder
{
    const NONE = '____NONE____';

    protected $class;
    protected $savePath;

    public function __construct()
    {
        $this->class = new ClassGenerator();
    }

    /**
     * @return ClassGenerator
     */
    public function getClassGenerator()
    {
        return $this->class;
    }

    /**
     * @param string $name
     */
    public function setClassName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('class name can not be empty');
        }

        $this->class->setName($name);
    }

    /**
     * @param boolean $isAbstract
     */
    public function setAbstract($isAbstract)
    {
        if ($isAbstract) {
            $this->class->setAbstract((boolean) $isAbstract);
        }
    }

    /**
     * @param string[] $interfaces
     */
    public function setInterfaces($interfaces = array())
    {
        if ($interfaces && is_array($interfaces)) {
            $this->class->setImplementedInterfaces($interfaces);
        }
    }

    /**
     * @param string $extends
     */
    public function setExtends($extends)
    {
        if (!empty($extends)) {
            $this->class->setExtendedClass($extends);
        }
    }

    /**
     * @param string $namespaceName
     */
    public function setNamespaceName($namespaceName)
    {
        if (!empty($namespaceName)) {
            $this->class->setNamespaceName($namespaceName);
        }
    }

    /**
     * @param string      $uses
     * @param null|string $alias
     */
    public function addUse($use, $alias = null)
    {
        if ($use) {
            $this->class->addUse($use, $alias);
        }
    }

    /**
     * @param string                   $propertyName
     * @param public|protected|private $modifier
     * @param boolean                  $isStatic
     * @param string                   $defaultValue
     * @param string                   $type
     */
    public function addProperty($propertyName, $modifer = 'public', $isStatic = false, $defaultValue = null, $type = PropertyValueGenerator::TYPE_AUTO)
    {
        $prop = new PropertyGenerator();
        $prop->setName($propertyName)
             ->setVisibility($modifer)
             ->setStatic($isStatic);

        if (!empty($defaultValue)) {
            $prop->setDefaultValue($defaultValue, $type);
        }

        $this->class->addPropertyFromGenerator($prop);

        $doc = new DocBlockGenerator();
        $tag = new Tag('var', $type);
        $doc->setTag($tag);
        $prop->setDocBlock($doc);

        $filter = new UnderscoreToCamelCase();

        $param = new \stdClass();
        $param->name = $propertyName;
        $param->type = $type;
        $param->defaultValue = static::NONE;
        $param->isByRef = false;

        if (!$isStatic) {
            $this->addMethod(
                'set'.ucfirst($filter->filter($propertyName)),
                'public',
                false,
                false,
                array($param),
                '$this->'.$propertyName.' = $'.$propertyName.';'
            );

            $getMethod = $this->addMethod(
                'get'.ucfirst($filter->filter($propertyName)),
                'public',
                false,
                false,
                array(),
                'return $this->'.$propertyName.';'
            );

            $doc = new DocBlockGenerator();
            $doc->setTag(new ReturnTag($type));
            $getMethod->setDocBlock($doc);
        }
    }

    /**
     * @param string                   $methodName
     * @param public|protected|private $modifier
     * @param boolean                  $isStatic
     * @param boolean                  $isFinal
     * @param array                    $parameters
     * @param string                   $content
     */
    public function addMethod($methodName, $modifer = 'public', $isStatic = false, $isFinal = false, $parameters = array(), $content = '')
    {
        $method = new MethodGenerator();
        $method->setName($methodName)
                ->setVisibility($modifer)
                ->setStatic((boolean) $isStatic)
                ->setFinal((boolean) $isFinal)
                ->setBody($content);

        if ($parameters) {
            $params = array();
            $doc = new DocBlockGenerator();
            foreach ($parameters as $p) {
                $tag = new ParamTag($p->name, $p->type);
                $doc->setTag($tag);

                $param = new ParameterGenerator();
                $param->setName($p->name)
                        ->setType($p->type)
                        ->setPassedByReference((boolean) $p->isByRef);

                if ($p->defaultValue !== static::NONE) {
                    $param->setDefaultValue($p->defaultValue);
                }

                $params[] = $param;
            }

            $method->setDocBlock($doc);
            $method->setParameters($params);
        }

        $this->class->addMethodFromGenerator($method);

        return $method;
    }

    /**
     * @param string
     */
    public function setSavePath($path)
    {
        $this->savePath = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }

    public function generate()
    {
        return $this->class->generate();
    }
}
