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
 * GoogleTranslate helper
 */
class Yireo_GoogleTranslate_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Switch to determine whether the extension is enabled or not
     *
     * @access public
     * @param null
     * @return boolean
     */
    public function enabled()
    {
        if ($this->hasApiSettings() == false) return false;
        return true;
    }

    /**
     * Check whether the API-details are configured
     *
     * @access public
     * @param null
     * @return string
     */
    public function hasApiSettings()
    {
        $apiKey = Mage::helper('googletranslate')->getApiKey2();
        $bork = Mage::getStoreConfig('catalog/googletranslate/bork');
        if (empty($apiKey) && empty($bork)) {
            return false;
        }
        return true;
    }

    /**
     * Return the API-key
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiKey2()
    {
        return Mage::getStoreConfig('catalog/googletranslate/apikey2');
    }

    /**
     * Return the customization ID
     *
     * @access public
     * @param null
     * @return string
     */
    public function getCustomizationId()
    {
        return Mage::getStoreConfig('catalog/googletranslate/customization_id');
    }

    /**
     * Return the text of the button label
     *
     * @access public
     * @param null
     * @return string
     */
    public function getButtonLabel()
    {
        $label = Mage::getStoreConfig('catalog/googletranslate/buttonlabel');
        $label = str_replace('$FROM', self::getFromTitle(), $label);
        $label = str_replace('$TO', self::getToTitle(), $label);
        return $label;
    }

    /**
     * Return the source language
     *
     * @access public
     * @param null
     * @return string
     */
    public function getFromLanguage()
    {
        $parent_locale = Mage::getStoreConfig('general/locale/code');
        $from_language = preg_replace('/_(.*)/', '', $parent_locale);
        return $from_language;
    }

    /**
     * Return the title of the source language
     *
     * @access public
     * @param null
     * @return string
     */
    public function getFromTitle()
    {
        $from_language = self::getFromLanguage();
        $from_title = Zend_Locale::getTranslation($from_language, 'language');
        return $from_title;
    }

    /**
     * Return the destination language
     *
     * @access public
     * @param null
     * @return string
     */
    public function getToLanguage($store = null)
    {
        if (empty($store)) {
            $store = Mage::app()->getRequest()->getUserParam('store');
        }

        $to_language = Mage::getStoreConfig('catalog/googletranslate/langcode', $store);
        if (empty($to_language)) {
            $locale = Mage::getStoreConfig('general/locale/code', $store);
            $to_language = preg_replace('/_(.*)/', '', $locale);
        }

        $controllerName = Mage::app()->getRequest()->getControllerName();
        if($controllerName == 'cms_block') {
            $blockId = Mage::app()->getRequest()->getParam('block_id');
            if($blockId > 0) {
                $block = Mage::getModel('cms/block')->load($blockId);
                $storeIds = $block->getStoreId();
                if(is_array($storeIds) && count($storeIds) == 1) {
                    $storeId = $storeIds[0];
                    $locale = Mage::getStoreConfig('general/locale/code', $storeId);
                    $to_language = preg_replace('/_(.*)/', '', $locale);
                }
            }
        } elseif($controllerName == 'cms_page') {
            $pageId = Mage::app()->getRequest()->getParam('page_id');
            if($pageId > 0) {
                $page = Mage::getModel('cms/page')->load($pageId);
                $storeIds = $page->getStoreId();
                if(is_array($storeIds) && count($storeIds) == 1) {
                    $storeId = $storeIds[0];
                    $locale = Mage::getStoreConfig('general/locale/code', $storeId);
                    $to_language = preg_replace('/_(.*)/', '', $locale);
                }
            }
        }

        return $to_language;
    }

    /**
     * Return the title of the destination language
     *
     * @access public
     * @param null
     * @return string
     */
    public function getToTitle()
    {
        $to_language = self::getToLanguage();
        $to_title = Zend_Locale::getTranslation($to_language, 'language');
        return $to_title;
    }

    /**
     * Return the title of the destination language
     *
     * @access public
     * @param null
     * @return string
     */
    public function getStoreByCode($code)
    {
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            if ($store->getCode() == $code) {
                return $store;
            }
        }
        return Mage::getModel('core/store');
    }
}
