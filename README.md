# TestMage

A very simple framework that will helps you creating PHPUnit test cases in Magento 1. Since it uses this library, you can use all the features provided plus all the Magento features.

This framework provides the necesary tools to allow you doing things like Mage::getModel('catalog/product') inside your tests without any issue, like you were in any model, controller or block.

In addition it provides a Mock Engine - called MockingMage - that allows you to Mock whatever Model or Object you want. 

The principal advantage of using this engine, is that the Object returned belongs to the class you wanna Mock, but rather then be an instance of the real class, is an instance of a Runtime Class (created on the fly) with the same name. This little hack, allows you to pass all type checks in the code with non-fuctionality object. Of course, you are able to mock functions to that Object too. 

Considering that the functions you are mocking, by definition, have no code, the only thing you can define is a return value. This is very useful because you probably don't want to test the class you are mocking (that's why you're doing it, after all), but you need that class to make your test work. Using this mocked functions you can overpass all the calls to the object without making unnecessary calls to database nor spending time in things you are not interested. 

## Installing TestMage
---
### Preconditions
* PHPUnit (https://phpunit.de/manual/current/en/installation.html)

### Using git submodules
1. From the Magento 1 root directory, run:


    git submodule add git@github.com:mserrano-praxisis/TestMage.git lib/TestMage

## Set up
---
The setup is very easy, and you need to do it only once:

1. Create a new "tests" directory in Magento 1 root directory. This way you have a new "tests" directory at the same level that app, lib, etc.
2. Prevent access to it. In apache you do this adding a new .htacess file inside the directory, with this content:
```    
Order deny,allow
Deny from all
```

## Creating you first test
---
Once you have the setup complete, you may want to create your first test case. All your tests are going to live inside the "tests" directory. 

*Note: for convention, we are going to use the same directory structure and class names that Magento uses. This way if you are going to test Mage_Catalog_Model_Customer, then you will need to create inside "tests" directory, the file Mage/Catalog/Model/CustomerTest.php*

First, we are going to create a new Model and then we are going to test it. It's no the goal of this guide teach you how to create a new Model, so if you don't know how to do it, please refer to a Magento guide.
```php
class Company_TestModule_Model_Calculator extends Mage_Core_Model_Abstract
{
    public function add($a, $b)
    {
        return $a + $b;
    }

    public function subtract($a, $b)
    {
        return $a - $b;
    }

    public function getGreater($a)
    {
        return $a + 2;
    }
}
```
Create a new Test class to test this Model:
1. Inside "tests" directory, create the file: Company/TestModule/Model/CalculatorTest.php with this content
```php
require_once (__DIR__.'/../../../../lib/TestMage/Testable.php');

class Company_TestModule_Model_CalculatorTest extends Testable
{
    protected $myModel;
    protected $number = 4;

    protected function setUp()
    {
        $this->myModel = Mage::getModel('company_testmodule/calculator');
    }

    public function testAdd()
    {
        $result = $this->myModel->add(2,5);
        $this->assertEquals(7, $result);
    }

    /**
     * @test
     */
    public function subtract()
    {
        $result = $this->myModel->subtract(2,5);
        $this->assertEquals(-3, $result);
    }

    public function testGetGreater()
    {
        $result = $this->myModel->getGreater(3);
        $this->assertGreaterThan(3,$result);
    }

    public function testBeforeNumber()
    {
        $this->assertEquals(3,$this->number);
    }

    /**
     * @before
     */
    public function setNumber()
    {
        $this->number = 3;
    }
}
```

I'll explain the most significant lines:

```php
class Company_TestModule_Model_CalculatorTest extends Testable
```

Testable is the class that makes the magic... It do all ugly stuff so you can simple call Mage::get... wherever you want... and makes you class recognizable by PHPUnit.

```php
protected function setUp()
```

If you are familirized with PHPUnit, you will know there it a method named setUp() that executes before the tests, and you can set preconditions there. You can make whatever precenditions you want... that all yours!!!

```php
public function testAdd()
```

Creates a new Test. All the funcitons that start with "test" will be consider a test. Please refer to PHPUnit documetantation, to know how to create them.

```php
/**
 * @test
 */
public function subtract()
```

Another way to create tests using notations.

```php
/**
 * @before
 */
public function setNumber()
```
This notation is used to create functions that will run before the tests.

### Mocking an Object
---
Mocking objects is extremely useful to test those kind of things where you need another objects to get data and extra stuff. Consider you wanna test a WebService and you need data from customers and orders. There is no way to load from database those objects: first of all because of speed (you will probably spend more time loading the data than testing the service itself), second, beacuse you probably wont test if the load is going ok... you just want an object with useful data to perfom the test, and thats it, no more no less.

Here is where MockingMage came to rescue you! Is a very simple static class that will generate an Object you that you can use for those cases. This particular object belongs to the class you want to mock, but is not an instance of it: is an instance of a Runtime Class created on the fly with exactly the same name you want, just that. The object itself has nothing to do with the real object or class, again, is not an instance of the real class, just an instance of a dummy class with the same name.

##### Ok, got it! Why?? What I do with that?
The reason is simple, you don't really need a real object, you only an object that fit the needs of the thing you are testing, thats all.

To create a new mock, please follow this steps:
1. First you need to include the MockinMage class in you php file.
```php
require_once (__DIR__.'/../../../../lib/TestMage/MockingMage.php');
```
2. Create the Object like this
```php
$newObj = MockingMage::getModel('catalog/product');
$newObj->mockFunction('getName', 'My Mocked Product');
```

That's it! now $newObj is your mocked product.

If you call get_class method for that object, in Magento standard, you will get "Mage_Catalog_Model_Product". If you print $newObj->getName(), you will get "My Mocked Product". Remember that the class of that object has nothing to do with the real class, is just a instance of a dummy class created on the fly, and will disappear as soon the test finishes.

##### What does that code do?
In the first line, you see something you know: Magento's factory. MockingMage is smart enough to look for the name of the class that Magento is using (just like Magento does) and retrieves a new Object from that class. So if you have rewrites overthere, don't worry about it, MockingMage will do the work for you.

MockingMage, have other functions like getSingleton(), helper() and getObject(). Please refer to class documentation to know how to use them.

The second line, is where you "add functionality" to the Object just created. Remember that the object is just a dummy, it does nothing from its own, so you should add all the necessary functions your test needs to run. This functions can only return something (and is the only thing you need, after all)

### How to run the tests
---

Running the tests are very easy, just go to to your Magento root diretory and run:

	phpunit tests

"tests" is the directory where you store all your tests.

