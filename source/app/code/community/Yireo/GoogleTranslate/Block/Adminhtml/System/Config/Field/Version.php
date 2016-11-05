<?php
/**
 * Yireo GoogleTranslate for Magento
 *
 * @package     Yireo_GoogleTranslate
 * @author      Yireo <info@yireo.com>
 * @copyright   2015 Yireo <https://www.yireo.com/>
 * @license     Open Source License (OSL v3)
 */

/**
 * GoogleTranslate version
 */
class Yireo_GoogleTranslate_Block_Adminhtml_System_Config_Field_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Override method to output our custom HTML with JavaScript
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->getVersion();

        return $html;
    }

    /**
     * @return string
     */
    protected function getVersion()
    {
        $config = Mage::app()->getConfig()->getModuleConfig('Yireo_GoogleTranslate');
        return (string)$config->version;
    }
}