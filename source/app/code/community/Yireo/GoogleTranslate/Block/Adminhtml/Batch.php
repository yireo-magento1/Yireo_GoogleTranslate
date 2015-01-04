<?php
/**
 * Yireo GoogleTranslate for Magento 
 *
 * @package     Yireo_GoogleTranslate
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * GoogleTranslate Batch-block
 */
class Yireo_GoogleTranslate_Block_Adminhtml_Batch extends Mage_Core_Block_Template
{
    protected $_items;

    protected $_itemIds;

    protected $_storeViews;

    protected $_attributes;

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
        if(empty($this->_itemIds)) {
            $type = $this->getRequest()->getParam('type');
            $key = $this->getRequest()->getParam('massaction_prepare_key');
            $this->_itemIds = $this->getRequest()->getParam($key);
        }

        return $this->_itemIds;
    }

    public function getItems()
    {
        if(empty($this->_items)) {

            $itemIds = $this->getItemIds();
    
            $type = $this->getRequest()->getParam('type');
            if($type == 'product') {
                $this->_items = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(array('name', 'sku'))
                    ->addAttributeToFilter('entity_id', array('IN' => $itemIds))
                ;
            }
        }

        return $this->_items;
    }

    public function getStoreViews()
    {
        if(empty($this->_storeViews)) {

            $this->_storeViews = Mage::getModel('core/store')->getCollection();

            $batchFilter = Mage::getStoreConfig('catalog/googletranslate/batch_stores');
            $batchFilter = explode(',' , $batchFilter);

            if(!empty($batchFilter)) {
                $this->_storeViews->addFieldToFilter('store_id', array('IN' => $batchFilter));
            }

            foreach($this->_storeViews as $store) {
                $locale = Mage::getStoreConfig('general/locale/code', $store);
                $locale = preg_replace('/_(.*)/', '', $locale);
                $store->setLocale($locale);
            }
        }

        return $this->_storeViews;
    }

    public function getAttributes()
    {
        if(empty($this->_attributes)) {

            $this->_attributes = Mage::getModel('googletranslate/system_config_source_attribute')->getCollection();
    
            $batchFilter = Mage::getStoreConfig('catalog/googletranslate/batch_attributes');
            $batchFilter = explode(',' , $batchFilter);

            if(!empty($batchFilter)) {
                $this->_attributes->addFieldToFilter('attribute_code', array('IN' => $batchFilter));
            }
        }

        return $this->_attributes;
    }

    public function getItemData()
    {
        $items = $this->getItems();
        $storeViews = $this->getStoreViews();
        $attributes = $this->getAttributes();

        $data = array();
        foreach($items as $item) {
            foreach($storeViews as $storeView) {
                foreach($attributes as $attribute) {
                    $data[] = $item->getId().'|'.$storeView->getId().'|'.$attribute->getAttributeCode();
                }
            }
        }

        return $data;
    }
}
