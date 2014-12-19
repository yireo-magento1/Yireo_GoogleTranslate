<?php
/**
 * Yireo GoogleTranslate for Magento 
 *
 * @package     Yireo_GoogleTranslate
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (C) 2014 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * GoogleTranslate Batch-block
 */
class Yireo_GoogleTranslate_Block_Adminhtml_Batch extends Mage_Core_Block_Template
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setData('area','adminhtml');
        $this->setTemplate('googletranslate/batch.phtml');
    }

    /**
     * Return the currently selected items
     * 
     * @return array
     */
    public function getItemIds()
    {
        $type = $this->getRequest()->getParam('type');
        $key = $this->getRequest()->getParam('massaction_prepare_key');
        $items = $this->getRequest()->getParam($key);

        return $items;
    }

    public function getItems()
    {
        $itemIds = $this->getItemIds();

        $type = $this->getRequest()->getParam('type');
        if($type == 'product') {
            $items = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(array('name', 'sku'))
                ->addAttributeToFilter('entity_id', array('IN' => $itemIds))
            ;
        }

        return $items;
    }

    public function getStoreViews()
    {
        $collection = Mage::getModel('core/store')->getCollection();

        $batchFilter = Mage::getStoreConfig('catalog/googletranslate/batch_stores');
        $batchFilter = explode(',' , $batchFilter);
        if(!empty($batchFilter)) {
            $collection->addFieldToFilter('store_id', array('IN' => $batchFilter));
        }

        foreach($collection as $store) {
            $locale = Mage::getStoreConfig('general/locale/code', $store);
            $locale = preg_replace('/_(.*)/', '', $locale);
            $store->setLocale($locale);
        }

        return $collection;
    }

    public function getAttributes()
    {
        $collection = Mage::getModel('googletranslate/system_config_source_attribute')->getCollection();

        $batchFilter = Mage::getStoreConfig('catalog/googletranslate/batch_attributes');
        $batchFilter = explode(',' , $batchFilter);
        if(!empty($batchFilter)) {
            $collection->addFieldToFilter('attribute_code', array('IN' => $batchFilter));
        }


        return $collection;
    }
}
