<?php

namespace CodeGen\Service;

use ConsoleEx\Prompt\SingleInput;
use ConsoleEx\Prompt\MultiInputPrompt;
use ConsoleEx\Prompt\PromptComposite;
use ConsoleEx\Prompt\Callback;
use Zend\Console\Console;
use Zend\Console\Prompt\Line;
use Zend\Console\Prompt\Select;
use Zend\Console\Prompt\Confirm;
use Zend\Console\Prompt\PromptInterface;

class ClassInput
{
    /**
     * @var ClassBuilder
     */
    protected $classBuilder;

    /**
     * @var array
     */
    protected $types = array('i' => 'int', 'b' => 'bool', 's' => 'string', 'f' => 'float', 'r' => 'resource', 'm' => 'mixed', 'o' => 'object');

    protected function __construct()
    {
        $this->classBuilder = new ClassBuilder();
    }

    protected function __clone()
    {
    }

    public static function init()
    {
        $obj = new Self();
        $obj->takeInput();
        return $obj->classBuilder;
    }

    /**
     * begin the user input
     */
    public function takeInput()
    {
        $this->askClassName()
             ->askIfAbstract()
             ->askNamespace()
             ->askUses()
             ->askExtends()
             ->askImplements()
             ->askProperties()
             ->askMethods();
    }

    /**
     * Ask user class name
     *
     * @return ClassInput
     */
    protected function askClassName()
    {
        Console::getInstance()->write($this->getHeading('PROVIDE CLASS NAME'));

        $input = new SingleInput(
            'Please enter class name: ',
            'Please enter a valid class name: ',
            true,
            function ($input) {
                return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $input);
            }
        );

        $name = $input->show();

        $this->classBuilder->setClassName(ucfirst($name));

        return $this;
    }

    /**
     * Ask user if the class is an abstract class
     *
     * @return ClassInput
     */
    protected function askIfAbstract()
    {
        Console::getInstance()->write($this->getHeading('IS CLASS ABSTRACT?'));

        $isAbstractPrompt = new Confirm('Is class abstract: (y/n) ');
        $isAbstract = $isAbstractPrompt->show();
        $this->classBuilder->setAbstract($isAbstract);

        return $this;
    }

    /**
     * Ask use the namespace of the class
     *
     * @return ClassInput
     */
    protected function askNamespace()
    {
        Console::getInstance()->write($this->getHeading('ADD NAMESPACE'));

        $input = new SingleInput(
            'Enter name space (optional): ',
            '',
            false,
            function ($input) {
                return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $input);
            }
        );

        $this->classBuilder->setNamespaceName(
            $input->show()
        );

        return $this;
    }

    /**
     * Ask user class "uses"
     *
     * @return ClassInput
     */
    protected function askUses()
    {
        Console::getInstance()->write($this->getHeading('ADD USES'));

        $usePrompt = new Callback(
            new Line('Enter "use" path (optional): ', true),
            function ($use) {
                if ($use) {
                    $alias = new Line('Enter alias (optional): ', true);

                    return array( $use, $alias->show());
                }

                return array();
            }
        );

        $askUse = new Callback(
            $usePrompt,
            function ($use) use ($usePrompt) {
                if ($use) {
                    $askMoreUse = new MultiInputPrompt(
                        $usePrompt,
                        array(),
                        'more uses? [y,n]',
                        true
                    );

                    $uses = $askMoreUse->show();

                    return array_merge(array($use), $uses);
                }

                return array();
            }
        );

        $data = $askUse->show();

        if ($data) {
            foreach ($data as $use) {
                $this->classBuilder->addUse($use[0], $use[1]);
            }
        }

        return $this;
    }

    /**
     * Ask user about the extended class
     *
     * @return ClassInput
     */
    protected function askExtends()
    {
        Console::getInstance()->write($this->getHeading('CLASS EXTENDS?'));

        $extendsPrompt = new Line('Class extends (optional): ', true);
        if ($extends = $extendsPrompt->show()) {
            $this->classBuilder->setExtends($extends);
        }

        return $this;
    }

    /**
     * Ask user if the class implements interfaces
     *
     * @return ClassInput
     */
    protected function askImplements()
    {
        Console::getInstance()->write($this->getHeading('CLASS IMPEMENTS INTERFACE(S)?'));

        $interfacePrompt = $this->promptIfTruePrompt(
            'Does the class implements interface(s): (y/n) ',
            new MultiInputPrompt(
                new Line('Enter the implementation interface name: ')
            )
        );

        $data = $interfacePrompt->show();
        $data = array_pop($data);
        $this->classBuilder->setInterfaces($data);

        return $this;
    }

    /**
     * Ask user about instance/class variables
     *
     * @return ClassInput
     */
    protected function askProperties()
    {
        Console::getInstance()->write($this->getHeading('ADD PROPERTIES'));

        $namePrompt = $this->getValidNamePrompt('Enter property name: ', 'Enter a valid property name: ', true);
        $visiblityAndStaticPrompt = $this->getVisiblityAndStaticPrompt();
        $defaultValuePrompt = new Line('Enter default value (optional): ', true);

        $propertyDetails = new PromptComposite();
        $propertyDetails->add($namePrompt)
                        ->add($visiblityAndStaticPrompt)
                        ->add($defaultValuePrompt)
                        ->add($this->getTypesPrompt());

        $multiProperties = new MultiInputPrompt($propertyDetails);

        $properties = $this->promptIfTruePrompt(
            'Has properties? (y/n)',
            $multiProperties
        )->show();

        $properties = array_pop($properties);

        if ($properties) {
            foreach ($properties as $property) {
                $name = $property[0];
                $visiblity = $property[1][0];
                $isStatic = $property[1][1];
                $defaultValue = $property[2];
                $type = $property[3];

                $this->classBuilder->addProperty($name, $visiblity, $isStatic, $defaultValue, $type);
            }
        }

        return $this;
    }

    /**
     * Ask user about the methods
     *
     * @return ClassInput
     */
    protected function askMethods()
    {
        Console::getInstance()->write($this->getHeading('ADD METHODS'));

        $namePrompt = $this->getValidNamePrompt('Enter method name: ', 'Enter a valid method name: ', true);
        $visiblityAndStaticPrompt = $this->getVisiblityAndStaticPrompt();
        $isMethodFinalPrompt = new Confirm('Is final? (y/n): ');

        $parameterNamePrompt = $this->getValidNamePrompt('Enter parameter name: ', 'Enter a valid parameter name: ', true);
        $parameterTypePrompt = $this->getTypesPrompt();

        $parameterDefaultPrompt = new Line('Enter parameter default value (optional): ', true);
        $parameterIsReferencedPrompt = new Confirm('Is passed by reference? (y/n): ');
        $parameterDetailsPrompt = new PromptComposite(array($parameterNamePrompt, $parameterTypePrompt, $parameterDefaultPrompt, $parameterIsReferencedPrompt));
        $multiParameterPrompts = new MultiInputPrompt($parameterDetailsPrompt, array(), 'Add more parameters? (y/n) ');

        $hasParametersPrompt = $this->promptIfTruePrompt(
            'Has Parameters? (y/n)',
            $multiParameterPrompts
        );

        $methodDetails = new PromptComposite();
        $methodDetails->add($namePrompt)
                        ->add($visiblityAndStaticPrompt)
                        ->add($isMethodFinalPrompt)
                        ->add($hasParametersPrompt);

        $multiMethodPrompts = new MultiInputPrompt($methodDetails, array(), 'Add more methods? (y/n) ');

        $methods = $this->promptIfTruePrompt(
            'Has methods? (y/n)',
            $multiMethodPrompts
        )->show();

        $methods = array_pop($methods);

        if ($methods) {
            foreach ($methods as $method) {
                $name = $method[0];
                $visiblity = $method[1][0];
                $isStatic = $method[1][1];
                $isFinal = $method[2];
                $params = array();

                if ($parameters = array_pop($method[3])) { // has params
                    foreach ($parameters as $param) {
                        $po = new \stdClass();

                        $po->name = $param[0];
                        $po->type = $param[1];
                        $po->defaultValue = $param[2];
                        $po->isByRef = $param[3];

                        $params[] = $po;
                    }
                }

                $this->classBuilder->addMethod($name, $visiblity, $isStatic, $isFinal, $params);
            }
        }

        return $this;
    }

    /**
     * Get the types prompt
     *
     * @return PromptInterface
     */
    protected function getTypesPrompt()
    {
        $types = $this->types;

        return new Callback(
                new Select(
                    'Select type: ',
                    $types + array('c' => 'custom')
                ),
                function ($value) use ($types) {
                    if (array_key_exists($value, $types)) {
                        return $types[$value];
                    }

                    $custom = new Line('Type custom name: ');

                    return $custom->show();
                }
            );
    }

    /**
     * Creates a assertion true prompt
     *
     * @return PromptInterface
     */
    protected function promptIfTruePrompt($msg, PromptInterface $prompt, $heading = '')
    {
        $promptIfTrue = new PromptComposite(new Callback(new Confirm($msg),
            function ($hasProperty) use ($prompt) {
                if ($hasProperty) {
                    return $prompt->show();
                }

                return array();
            }

        ));

        if ($heading) {
            $promptIfTrue->setPromptText($heading);
        }

        return $promptIfTrue;
    }

    /**
     * Takes a valid naming rule input prompt
     *
     * @return PromptInterface
     */
    protected function getValidNamePrompt($validMsg, $invalidMsg, $isRequired)
    {
        $promptPropertyName = new SingleInput($validMsg, $invalidMsg, $isRequired,
            function ($input) {
                return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $input);
            }
        );

        return $promptPropertyName;
    }

    /**
     * Creates a prompt which takes visiblity and static information
     *
     * @return PromptInterface
     */
    protected function getVisiblityAndStaticPrompt()
    {
        $promptModifier = new Select('Please select modifier: ', array(
                'p' => 'public',
                'r' => 'protected',
                'v' => 'private',
        ));

        $filteredModifier = new Callback($promptModifier,
            function ($data) {
                switch ($data) {
                    case 'p':
                        $data = 'public';
                        break;
                    case 'r':
                        $data = 'protected';
                        break;
                    case 'v':
                        $data = 'private';
                        break;
                }

                return $data;
            }
        );

        $promptIsStatic = new Confirm('is static? (y/n): ');

        $visiblityAndStatic = new PromptComposite();

        return $visiblityAndStatic->add($filteredModifier)
                                  ->add($promptIsStatic);
    }

    /**
     * Creates a headding with 80 characters on each row and text centered
     *
     * @return string
     */
    protected function getHeading($msg)
    {
        $len = strlen($msg);
        $leftSpaces = round(38 - $len + $len/2);
        $rightSpaces = 80-($leftSpaces+$len)-2;

        return PHP_EOL.str_repeat('*', 80).PHP_EOL.
             '*'.str_repeat(' ', $leftSpaces).
             $msg.
             str_repeat(' ', $rightSpaces).'*'.
             PHP_EOL.str_repeat('*', 80).
             PHP_EOL.PHP_EOL;
    }
}
