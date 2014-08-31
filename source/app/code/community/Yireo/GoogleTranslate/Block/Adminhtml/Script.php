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
 * GoogleTranslate Script-block
 */
class Yireo_GoogleTranslate_Block_Adminhtml_Script extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setData('area','adminhtml');
    }

    /*
     * Return a specific URL
     * 
     * @access public
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route='', $params=array())
    {
        return Mage::getModel('adminhtml/url')->getUrl($route, $params);
    }

    /*
     * Return the configured API key version 2
     * 
     * @access public
     * @param null
     * @return string
     */
    public function getApiKey2()
    {
        return Mage::helper('googletranslate')->getApiKey2();
    }

    /*
     * Return the configured API key
     * 
     * @access public
     * @param null
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('googletranslate/index/'.$this->getPageType());
    }
}
