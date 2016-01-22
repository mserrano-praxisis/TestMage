<?php
/**
 * @author mserrano
 * @date 1/20/16
 */

require_once 'app/Mage.php';

class Testable extends PHPUnit_Framework_TestCase
{
    public static final function setUpBeforeClass()
    {
        Mage::app(static::getStore(), static::getType(), static::getOptions());
    }

    /**
     * Get the Magento's application store
     * Overwrite it to change the store
     * 'default' bu default
     *
     * @return string
     */
    protected static function getStore()
    {
        return 'default';
    }

    /**
     * Get the Magento's application type
     * Overwrite it to change the type
     * 'store' by default
     *
     * @return string
     */
    protected static function getType()
    {
        return 'store';
    }

    /**
     * Get the Magento's application options
     * Overwrite it to change the options
     * empty array by default
     *
     * @return array
     */
    protected static function getOptions()
    {
        return array();
    }
}