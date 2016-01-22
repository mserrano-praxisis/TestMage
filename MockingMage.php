<?php
/**
 * This class will provide the functionality to mock objects for the test you are doing
 *
 * @author mserrano
 * @date 1/20/16
 */

require_once 'app/Mage.php';

class MockingMage
{

    /**
     * Retrieves the default runtime class definition.
     *
     * @param $class
     * @param $extends
     * @param $overwriteCall
     * @return string
     */
    private static function getClassDefinition($class, $extends, $overwriteCall)
    {
        $definition = 'class ' . htmlspecialchars($class);
        $definition .= !empty($extends) ? ' extends ' . htmlspecialchars($extends) : '';
        $definition .=' {';
        $definition .= $overwriteCall ? '
            private $functions = array();

            public function mockFunction($name, $action)
            {
                $this->functions[$name] = $action;
            }

            function __call($name, $arguments)
            {
                if (array_key_exists($name, $this->functions)) {
                    return $this->functions[$name];
                } else {
                    throw new Exception("The method $name does not exist");
                }
            }
        };' : '};';

        return $definition;
    }

    /**
     * This method will helps you create a Mock Object. You can set the class you want to mock and even extends from
     * another. If you extends from Varien_Object, you may want to set $overwriteCall in false in order to use the
     * magic methods. Doing that, you wont be able to add new mock functions to the object.
     *
     * The new Mock Object, will match the type of the real object but not the functionality. You cannot add functionality
     * but you can set default responses (and always the same) for the methods or functions you want.
     *
     * Example:
     * Suppose you need a customer to test a WebService, but you don't want to load an existent customer. In addition,
     * you need that the methods: getName, getAddress, isWoman and avgSalary, returns useful values so you can test the
     * WebService without problems. Your needs are:
     * name --> Alicia
     * address --> 1745 Broadway, New York, NY 10106
     * woman --> true
     * avg salary --> 2000
     *
     * $customer = MockingMage::getObject('Mage_Customer_Model_Customer');
     * $customer->mockFunction('getName', 'Alicia');
     * $customer->mockFunction('getAddress', '1745 Broadway, New York, NY 10106');
     * $customer->mockFunction('isWoman', true);
     * $customer->mockFunction('avgSalary', 2000);
     *
     * That's it! now you have a new Mock Customer, that will act as a real customer (matching types) and will respond
     * always the same values for the functions you set. These object live in memory, any file nor temporary storage
     * will be used and are totally volatile so you don't need to care about them.
     *
     * @param string $class the class you want to mock.
     * @param string $extends the class you want to extend from (optional).
     * @param bool|true $overwriteCall true if you want to overwrite the __call method (default). If you set in false,
     * you wont be able to mock any function. This is useful if you want to mock a VarienObject and use the magic methods.
     * @return mixed
     */
    public static function getObject($class, $extends = '', $overwriteCall = true)
    {
        eval(self::getClassDefinition($class, $extends, $overwriteCall));

        $obj = new $class();
        return $obj;
    }

    /**
     * This function works the same as getObject, but you can use Magento's factory class name instead.
     *
     * If you want to mock a product you can use 'catalog/product' instead and this method will look for the correct
     * class name and will give a mock instance of it.
     *
     * @param $modelClass
     * @param string $extends
     * @param bool|true $overwriteCall
     * @return mixed
     */
    public static function getModel($modelClass, $extends = '', $overwriteCall = true)
    {
        $class = Mage::getConfig()->getModelClassName($modelClass);
        return self::getObject($class, $extends, $overwriteCall);
    }

    /**
     * This function works the same as getObject, but you can use Magento's factory class name instead.
     *
     * If you want to mock a product you can use 'catalog/product' instead and this method will look for the correct
     * class name and will give a mock instance of it.
     *
     * For this particular case, getSingleton is just and alias for getModel
     *
     * @param $modelClass
     * @param string $extends
     * @param bool|true $overwriteCall
     * @return mixed
     */
    public static function getSingleton($modelClass, $extends = '', $overwriteCall = true)
    {
        return self::getModel($modelClass, $extends, $overwriteCall);
    }

    /**
     * This function works the same as getObject, but you can use Magento's factory class name instead.
     *
     * If you want to mock a catalog helper you can use 'catalog' instead and this method will look for the correct
     * class name and will give a mock instance of it.
     *
     * @param $modelClass
     * @param string $extends
     * @param bool|true $overwriteCall
     * @return mixed
     */
    public static function helper($modelClass, $extends = '', $overwriteCall = true)
    {
        $class = Mage::getConfig()->getHelperClassName($modelClass);
        return self::getObject($class, $extends, $overwriteCall);
    }
}