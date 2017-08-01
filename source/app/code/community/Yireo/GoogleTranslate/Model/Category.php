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
 * GoogleTranslate Category model
 */
class Yireo_GoogleTranslate_Model_Category extends Yireo_GoogleTranslate_Model_Entity
{
    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $entity;

    /**
     * @param string $attribute
     * @param string $store
     *
     * @return string
     */
    protected function getStoreValue($attribute, $store)
    {
        $currentValue = Mage::getResourceModel('catalog/category')->getAttributeRawValue($this->entity->getId(), $attribute, $store);
        return trim($currentValue);
    }

    /**
     * @return string
     */
    protected function getEntityLabel()
    {
        return $this->entity->getId();
    }

    /**
     * @return string
     */
    protected function getEntityType()
    {
        return 'category';
    }
}
