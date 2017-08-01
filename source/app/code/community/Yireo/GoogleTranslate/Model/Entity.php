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
abstract class Yireo_GoogleTranslate_Model_Entity
{
    /**
     * @var Mage_Core_Model_Abstract
     */
    protected $entity;

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
     * Allow translation
     *
     * @var boolean
     */
    protected $allowTranslation = true;

    /**
     * @var int
     */
    protected $delay = 0;

    /**
     * Counter of characters
     *
     * @var int
     */
    protected $charCount = 0;


    /**
     * Yireo_GoogleTranslate_Model_Product constructor.
     */
    public function __construct()
    {
        $this->translator = Mage::getSingleton('googletranslate/translator');
        $this->helper = Mage::helper('googletranslate');
        $this->store = Mage::getModel('core/store');
    }

    /**
     * Method to translate specific attributes of a specific product
     *
     * @param Mage_Core_Model_Abstract $entity
     * @param array $attributes
     * @param array $stores
     */
    public function translate(Mage_Core_Model_Abstract $entity, $attributes, $stores)
    {
        // Set the entity
        $this->entity = $entity;
        $this->entity = $this->entity->load($this->entity->getId());

        // Reset some values
        $this->charCount = 0;

        // Get the parent-locale
        $this->parentLanguage = $this->getParentLanguage();

        // Loop through the stores
        foreach ($stores as $store) {
            $this->translateEntityByStore($store, $attributes);

            if ($this->delay > 0) {
                sleep((int)$this->delay);
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param string $parentLanguage
     */
    public function setParentLanguage($parentLanguage)
    {
        $this->parentLanguage = $parentLanguage;
    }

    /**
     * @param boolean $allowTranslation
     */
    public function setAllowTranslation($allowTranslation)
    {
        $this->allowTranslation = (bool)$allowTranslation;
    }

    /**
     * @param int $delay
     */
    public function setDelay($delay)
    {
        $this->delay = (int)$delay;
    }

    /**
     * Method to return the current character count
     *
     * @return int
     */
    public function getCharCount()
    {
        return (int)$this->charCount;
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
     * @return string
     */
    protected function getParentLanguage()
    {
        $parentLocale = Mage::getStoreConfig('general/locale/code');
        return preg_replace('/_(.*)/', '', $parentLocale);
    }

    /**
     * @param $store
     * @param $attributes
     */
    protected function translateEntityByStore($store, $attributes)
    {
        $store = $this->sanitizeStore($store);

        // Load the entity into this store-scope
        $this->entity->setStoreId($store->getId());

        // Loop through the attributes
        foreach ($attributes as $attribute) {
            $this->translateAttribute($attribute, $store);
        }

        // Resave entire product
        $this->save();
    }

    /**
     * @param string $attribute
     * @param Mage_Core_Model_Store $store
     *
     * @return bool
     */
    protected function translateAttribute($attribute, $store)
    {
        // Log
        $log = $this->helper->__('Translating attribute "%s" of %s "%s" for store "%s"', $attribute, $this->getEntityType(), $this->getEntityLabel(), $store->getName());
        $this->helper->log($log);

        // Load both the global-value as the store-value
        $parentValue = $this->getParentValue($attribute);
        $currentValue = $this->getStoreValue($attribute, $store);

        try {
            $translatedValue = $this->translateAttributeValue($parentValue, $currentValue, $store);
        } catch (Exception $e) {
            $this->helper->log($this->helper->__('API-error for %s "%s": %s', $this->getEntityType(), $this->getEntityLabel(), $e->getMessage()));
            return false;
        }

        if (!empty($translatedValue)) {
            $this->entity->setData($attribute, $translatedValue);
            $this->entity->getResource()->saveAttribute($this->entity, $attribute);
        }

        // Increment the total-chars
        $this->charCount = $this->charCount + strlen($parentValue);

        return true;
    }

    /**
     * @param $parentValue
     * @param $currentValue
     * @param $store
     *
     * @return bool|string
     */
    protected function translateAttributeValue($parentValue, $currentValue, $store)
    {
        if (empty($parentValue)) {
            $this->helper->log($this->helper->__('Empty parent value, so skipping'));
            return false;
        }

        // Overwrite existing values
        if ($parentValue != $currentValue && $this->doOverwriteExistingValues() === false) {
            $this->helper->log($this->helper->__('Existing value, so skipping'));
            return false;
        }

        // Translate the value
        if ($this->allowTranslation == false) {
            $this->charCount = $this->charCount + strlen($parentValue);
            return false;
        }

        $currentLanguage = $this->helper->getToLanguage($store);
        $translatedValue = $this->translator->translate($parentValue, $this->parentLanguage, $currentLanguage);

        $apiError = $this->translator->getApiError();
        if (!empty($apiError)) {
            throw new RuntimeException($apiError);
        }

        return $translatedValue;
    }

    /**
     * @return mixed
     */
    protected function getEntityLabel()
    {
        return $this->entity->getEntityId();
    }

    /**
     * Save the entity
     */
    protected function save()
    {
        if ($this->allowTranslation == true) {
            $this->entity->save();
        }
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
     * @return bool
     */
    protected function doOverwriteExistingValues()
    {
        return (bool)$this->getStoreConfig('catalog/googletranslate/overwrite_existing');
    }

    /**
     * @param $attribute
     *
     * @return mixed
     */
    protected function getParentValue($attribute)
    {
        return $this->getStoreValue($attribute, Mage_Core_Model_App::ADMIN_STORE_ID);
    }

    /**
     * @param $attribute
     * @param $store
     *
     * @return string
     */
    abstract protected function getStoreValue($attribute, $store);

    /**
     * @return mixed
     */
    abstract protected function getEntityType();
}
