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
 * GoogleTranslate CMS Block
 */
class Yireo_GoogleTranslate_Model_Cms_Block extends Yireo_GoogleTranslate_Model_Entity
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $productTarget;

    /**
     * @var Mage_Catalog_Model_Resource_Product
     */
    protected $productResourceModel;

    /**
     * Yireo_GoogleTranslate_Model_Cms_Block constructor.
     */
    public function __construct()
    {
        $this->blockTarget = Mage::getModel('cms/block');
        $this->blockResourceModel = Mage::getResourceModel('cms/block');

        parent::__construct();
    }

    /**
     * Method to translate specific attributes of a specific product
     *
     * @param Mage_Cms_Block_Block $block
     * @param array $blockAttributes
     * @param array $stores
     * @param int $delay
     * @param bool $allowTranslation
     */
    public function translate(Mage_Cms_Block_Block $block, $blockAttributes, $stores, $delay = 0, $allowTranslation = null)
    {
        // Reset some values
        $this->charCount = 0;

        if (is_bool($allowTranslation)) {
            $this->allowTranslation = $allowTranslation;
        }

        /** @var Mage_Cms_Block_Block $block */
        $block = $this->productTarget->load($block->getId());

        // Loop through the stores
        foreach ($stores as $store) {

            $store = $this->sanitizeStore($store);
            $block->setStoreId($store->getId());

            $this->translateBlockAttributes($block, $store, $blockAttributes);

            if ($this->allowTranslation === true) {
                $block->save();
            }

            // Artificial sleep to give the API a rest
            if ($delay > 0) {
                sleep((int)$delay);
            }
        }
    }

    /**
     * @param Mage_Cms_Block_Block $block
     * @param Mage_Core_Model_Store $store
     * @param array $productAttributes
     */
    protected function translateBlockAttributes(Mage_Cms_Block_Block $block, Mage_Core_Model_Store $store, $productAttributes = [])
    {
        // Loop through the attributes
        foreach ($productAttributes as $productAttribute) {
            $this->translateProductAttribute($block, $store, $productAttribute);
        }
    }

    /**
     * @param Mage_Cms_Block_Block $block
     * @param Mage_Core_Model_Store $store
     * @param $productAttribute
     *
     * @return bool
     */
    protected function translateProductAttribute(Mage_Cms_Block_Block $block, Mage_Core_Model_Store $store, $blockAttribute)
    {
        // Log
        $log = $this->helper->__('Translating attribute "%s" of "%s" for store "%s"', $blockAttribute, $block->getSku(), $store->getName());
        $this->helper->log($log);

        // Reset some values
        $translatedValue = null;

        $currentLanguage = $this->helper->getToLanguage($store);

        // Load both the global-value as the store-value
        $parentValue = $this->productResourceModel->getAttributeRawValue($block->getId(), $productAttribute, Mage_Core_Model_App::ADMIN_STORE_ID);
        $currentValue = $this->productResourceModel->getAttributeRawValue($block->getId(), $productAttribute, $store);

        // Sanity checks
        $parentValue = trim($parentValue);
        $currentValue = trim($currentValue);

        if (empty($parentValue)) {
            $this->helper->log($this->helper->__('Empty parent value, so skipping'));
            return false;
        }

        // Overwrite existing values
        if ($parentValue !== $currentValue && (bool)$this->doOverwriteExistingValues() === false) {
            $this->helper->log($this->helper->__('Existing value, so skipping'));
            return false;
        }

        // Increment the total-chars
        $this->charCount = $this->charCount + strlen($parentValue);

        // Skip actual translation of this value
        if ($this->allowTranslation === false) {
            return false;
        }

        // Translate the value
        $translatedValue = $this->translator->translate($parentValue, $this->parentLanguage, $currentLanguage);

        // Detect API errors
        $apiError = $this->translator->getApiError();
        if (!empty($apiError)) {
            $this->helper->log($this->helper->__('API-error for %s: %s', $block->getSku(), $apiError));
            return false;
        }

        // Return empty values
        if (empty($translatedValue)) {
            return false;
        }

        // Save values
        $block->setData($productAttribute, $translatedValue);
        $block->getResource()->saveAttribute($block, $productAttribute);

        return true;
    }
}
