<?php
/**
 * Yireo GoogleTranslate for Magento
 *
 * @package     Yireo_GoogleTranslate
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * GoogleTranslate Product-extension
 */
class Yireo_GoogleTranslate_Model_Entity
{
    /**
     * Allow translation
     *
     * @var boolean
     */
    protected $allowTranslation = true;

    /**
     * Counter of characters
     *
     * @var int
     */
    protected $charCount = 0;

    /**
     * @var Yireo_GoogleTranslate_Model_Translator
     */
    protected $translator;

    /**
     * @var Yireo_GoogleTranslate_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * @var string
     */
    protected $parentLanguage = '';

    /**
     * Yireo_GoogleTranslate_Model_Product constructor.
     */
    public function __construct()
    {
        $this->translator = Mage::getSingleton('googletranslate/translator');
        $this->helper = Mage::helper('googletranslate');
        $this->store = Mage::getModel('core/store');

        $this->setParentLanguage();
    }

    /**
     * Method to return the current character count
     *
     * @return int
     */
    public function getCharCount()
    {
        return $this->charCount;
    }

    /**
     * Method to toggle the flag which allows translation
     *
     * @param bool $allowTranslation
     *
     * @return bool
     */
    public function allowTranslation($allowTranslation)
    {
        return $this->allowTranslation = (bool)$allowTranslation;
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    protected function getStoreConfig($path)
    {
        return Mage::getStoreConfig($path);
    }

    /**
     * @param mixed $store
     *
     * @return Mage_Core_Model_Store
     */
    protected function sanitizeStore($store)
    {
        if (is_object($store) && $store instanceof Mage_Core_Model_Store) {
            return $store;
        }

        if (is_numeric($store)) {
            return $this->store->load($store);
        }

        if (is_string($store)) {
            return $this->helper->getStoreByCode($store);
        }

        throw new InvalidArgumentException('Invalid store argument');
    }

    /**
     *
     */
    protected function setParentLanguage()
    {
        $parentLocale = $this->getStoreConfig('general/locale/code');
        $this->parentLanguage = preg_replace('/_(.*)/', '', $parentLocale);
    }

    /**
     * @return bool
     */
    protected function doOverwriteExistingValues()
    {
        return (bool)$this->getStoreConfig('catalog/googletranslate/overwrite_existing');
    }
}
