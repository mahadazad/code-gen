PHP Code Generator Utility
===========================

A great utility to generate classes. Speed up your development by generating classes directly from the console/terminal by simply answering to questions.

Usage
=====
```
php index.php class           create classes
```

Example Input:
==============
```shell
********************************************************************************
*                              PROVIDE CLASS NAME                              *
********************************************************************************

Please enter class name: Hello

********************************************************************************
*                              IS CLASS ABSTRACT?                              *
********************************************************************************

Is class abstract: (y/n) y

********************************************************************************
*                                 ADD NAMESPACE                                *
********************************************************************************

Enter namespace (optional): Hello\World

********************************************************************************
*                                   ADD USES                                   *
********************************************************************************

Enter "use" path (optional): use Abc\Xyz\Test
Enter alias (optional): AbcTest
more uses? [y,n]

********************************************************************************
*                                CLASS EXTENDS?                                *
********************************************************************************

Class extends (optional): HelloBase

********************************************************************************
*                         CLASS IMPEMENTS INTERFACE(S)?                        *
********************************************************************************

Does the class implements interface(s): (y/n) y
Enter the implementation interface name: HelloInterface
more? [y,n]

********************************************************************************
*                                ADD PROPERTIES                                *
********************************************************************************

Has properties? (y/n)y
Enter property name: name
Please select modifier: 
  p) public
  r) protected
  v) private
is static? (y/n): n
Enter default value (optional): mahad
Select type: 
  i) int
  b) bool
  s) string
  f) float
  r) resource
  m) mixed
  o) object
  c) custom
more? [y,n]

********************************************************************************
*                                  ADD METHODS                                 *
********************************************************************************

Has methods? (y/n)y
Enter method name: sayHello
Please select modifier: 
  p) public
  r) protected
  v) private
is static? (y/n): n
Is final? (y/n): n
Has Parameters? (y/n)n
Add more methods? (y/n) 

********************************************************************************
*                              FILE GENERATED AT:                              *
*                       /home/mahad/Desktop/Hello.php                          *
********************************************************************************
```

Generated Code:
===============
```php
namespace Hello\World;

use use Abc\Xyz\Test as AbcTest;

abstract class Hello extends HelloBase implements HelloInterface
{

    /**
     * @var string
     */
    protected $name = 'mahad';

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function sayHello()
    {
    }
    
}
```

Compile the PHAR file
=====================
You can create a .phar file by running the following command.

```
./bin/create-phar
```

Accessing the phar globally
===========================
```
sudo chmod 0777 ./bin/php-code-gen.phar
mv ./bin/php-code-gen.phar /usr/local/bin/php-code-gen
```
