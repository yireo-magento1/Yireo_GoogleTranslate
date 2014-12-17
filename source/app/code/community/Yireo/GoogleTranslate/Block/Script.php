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
class Yireo_GoogleTranslate_Block_Script extends Mage_Core_Block_Template
{
    /**
     * Return the customization ID
     * 
     * @access public
     * @param null
     * @return string
     */
    public function getCustomizationId()
    {
        return Mage::helper('googletranslate')->getCustomizationId();
    }

    /**
     * Allow translation
     * 
     * @access public
     * @param null
     * @return bool
     */
    public function allowTranslation()
    {
        return true; // @todo: Disable on specific pages?
    }
}
