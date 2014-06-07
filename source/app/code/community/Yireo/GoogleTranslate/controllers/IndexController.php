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
 * GoogleTranslate admin controller
 *
 * @category   GoogleTranslate
 * @package     Yireo_GoogleTranslate
 */
class Yireo_GoogleTranslate_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * AJAX callback for products
     *
     * @access public
     * @param null
     * @return null
     */
    public function productAction()
    {
        // Load the initial data, and don't continue if this fails
        if($this->preload() == false) {
            return;
        }

        // Load the correct data-model
        $translator = $this->getTranslator();
        $id = $translator->getData('id');
        $store = $translator->getData('store');

        $product = Mage::getModel('catalog/product')->setStoreId($store)->load($id);
        if(!$product->getId() > 0) {
            return $this->sendError($this->__('No product ID given'));
        }

        // Load the attribute-value
        $attribute = $translator->getData('attribute');
        $text = $product->getData($attribute);
        if(empty($text)) {
            return $this->sendError($this->__('No product-data found for attribute %s', $attribute));
        }
        $translator->setData('text', $text);

        // Make the request to the API
        $this->translate();
    }

    /**
     * AJAX callback for categories
     *
     * @access public
     * @param null
     * @return null
     */
    public function categoryAction()
    {
        // Load the initial data, and don't continue if this fails
        if($this->preload() == false) {
            return;
        }

        // Load the correct data-model
        $translator = $this->getTranslator();
        $id = $translator->getData('id');
        $store = $translator->getData('store');

        $category = Mage::getModel('catalog/category')->setStoreId($store)->load($id);
        if(!$category->getId() > 0) {
            return $this->sendError($this->__('No category ID given'));
        }

        // Load the attribute-value
        $attribute = $translator->getData('attribute');
        $text = $category->getData($attribute);
        if(empty($text)) {
            return $this->sendError($this->__('No category-data found for attribute %s', $attribute));
        }
        $translator->setData('text', $text);

        // Make the request to the API
        $this->translate();
    }

    /**
     * AJAX callback for CMS-pages
     *
     * @access public
     * @param null
     * @return null
     */
    public function pageAction()
    {
        // Load the initial data, and don't continue if this fails
        if($this->preload() == false) {
            return;
        }

        // Load the correct data-model
        $translator = $this->getTranslator();
        $id = $translator->getData('id');
        $store = $translator->getData('store');

        $page = Mage::getModel('cms/page')->setStoreId($store)->load($id);
        if(!$page->getId() > 0) {
            return $this->sendError($this->__('No CMS-page ID given'));
        }

        // Load the attribute-value
        $attribute = $translator->getData('attribute');
        $text = $page->getData($attribute);
        if(empty($text)) {
            return $this->sendError($this->__('No page-data found for attribute %s', $attribute));
        }
        $translator->setData('text', $text);

        // Make the request to the API
        $this->translate();
    }

    /**
     * AJAX callback for CMS-blocks
     *
     * @access public
     * @param null
     * @return null
     */
    public function blockAction()
    {
        // Load the initial data, and don't continue if this fails
        if($this->preload() == false) {
            return;
        }

        // Load the correct data-model
        $translator = $this->getTranslator();
        $id = $translator->getData('id');
        $store = $translator->getData('store');
        $block = Mage::getModel('cms/block')->setStoreId($store)->load($id);
        if(!$block->getId() > 0) {
            return $this->sendError($this->__('No CMS-block ID given'));
        }

        // Load the attribute-value
        $attribute = $translator->getData('attribute');
        $text = $block->getData($attribute);
        if(empty($text)) {
            return $this->sendError($this->__('No block-data found for attribute %s', $attribute));
        }
        $translator->setData('text', $text);

        // Make the request to the API
        $this->translate();
    }

    /**
     * Perform some sanity checks
     *
     * @access protected
     * @param null
     * @return null
     */
    protected function preload()
    {
        $id = $this->getRequest()->getParam('id');
        $attribute = $this->getRequest()->getParam('attribute');
        $fromLang = $this->getRequest()->getParam('from');
        $toLang = $this->getRequest()->getParam('to');
        $store = $this->getRequest()->getParam('store');

        // Sanity checks
        if(!$id > 0 || empty($attribute) || empty($fromLang) || empty($toLang)) {
            return $this->sendError($this->__('Wrong parameters'));
        }

        // Set the language to empty, when 
        if($fromLang == $toLang) {
            $fromLang = null;
        }

        // Check for the API-key
        $apiKey = Mage::helper('googletranslate')->getApiKey2();
        if(empty($apiKey)) {
            return $this->sendError($this->__('No API key'));
        }

        // Set these variables for use with the translator
        $translator = $this->getTranslator();
        $translator->setData('id', $id);
        $translator->setData('attribute', $attribute);
        $translator->setData('fromLang', $fromLang);
        $translator->setData('toLang', $toLang);
        $translator->setData('store', $store);
        $translator->setData('apiKey', $apiKey);
        return true;
    }

    public function getTranslator()
    {
        return Mage::getSingleton('googletranslate/translator');
    }

    /**
     * Method to call upon the Google API
     *
     * @access protected
     * @param null
     * @return null
     */
    protected function translate()
    {
        $translator = $this->getTranslator();
        $text = $translator->translate();

        if($translator->hasApiError()) {
            return $this->sendError($translator->getApiError());
        }

        return $this->sendTranslation($text);
    }

    /** 
     * Helper method to send a specific error
     *
     * @access protected
     * @param string $message
     * @return null
     */
    protected function sendError($message = null) 
    {
        $result = array('error' => $message);
        echo json_encode($result);
        return false;
    }

    /** 
     * Helper method to send the translation
     *
     * @access protected
     * @param string $translation
     * @return null
     */
    protected function sendTranslation($translation = null) 
    {
        $result = array('translation' => $translation);
        echo json_encode($result);
        return;
    }
}

