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
     * @var Mage_Cms_Model_Block
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
        /** @var Mage_Cms_Model_Mysql4_Block $cmsModel */
        $cmsModel = Mage::getModel('cms/block')->load($this->entity->getId());
        $currentValue = $cmsModel->getData($attribute);
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
        return 'cms_block';
    }
}
