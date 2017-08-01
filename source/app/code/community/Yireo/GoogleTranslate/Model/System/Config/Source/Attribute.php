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
 * Class Yireo_GoogleTranslate_Model_System_Config_Source_Attribute
 */
class Yireo_GoogleTranslate_Model_System_Config_Source_Attribute
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {

            $this->_options = array();
            $collection = $this->getCollection();
            foreach ($collection as $attribute) {
                $this->_options[] = array(
                    'value' => $attribute->getName(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }

        }
        return $this->_options;
    }

    /**
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getCollection()
    {
        $product = Mage::getModel('catalog/product');
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($product->getResource()->getTypeId())
            ->addFieldToFilter('frontend_input', array('IN' => array('text', 'textarea')))
            ->addFieldToFilter('backend_type', array('IN' => array('text', 'varchar')));

        return $collection;
    }
}
