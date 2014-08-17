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
 * GoogleTranslate observer
 */
class Yireo_GoogleTranslate_Model_Translator extends Mage_Core_Model_Abstract
{
    protected $apiUrl = 'https://www.googleapis.com/language/translate/v2';

    protected $apiError = null;

    protected $apiTranslation = null;

    /**
     * Method to call upon the Google API
     */
    public function translate($text = null, $fromLang = null, $toLang = null)
    {
        // Bork debugging
        if(Mage::getStoreConfig('catalog/googletranslate/bork')) {
            $wordCount = (int)str_word_count($text);
            if($wordCount < 4) $wordCount = 4;
            $this->apiTranslation = str_repeat('bork ', $wordCount);
            return $this->apiTranslation;
        }

        // Demo
        $apiKey = Mage::helper('googletranslate')->getApiKey2();
        if($apiKey == 'DEMO') {
            $this->apiError = Mage::helper('googletranslate')->__('API-translation is disabled for this demo');
            return false;
        }

        // Load some variables
        if(empty($text)) $text = $this->getData('text');
        if(empty($fromLang)) $fromLang = $this->getData('from');
        if(empty($toLang)) $toLang = $this->getData('toLang');

        // Exception when toLang is wrong
        if(empty($toLang) || $toLang == 'auto') {
            $this->apiError = Mage::helper('googletranslate')->__('Translation-target is wrong ['.$toLang.']');
            return false;
        }

        $apiKey = Mage::helper('googletranslate')->getApiKey2();
        $headers = array();

        // Google API fields
        $post_fields = array(
            'key' => $apiKey,
            'target' => $toLang,
            'source' => $fromLang,
            'format' => 'html',
            'prettyprint' => '1',
            'q' => $text,
        );
        //Mage::log('GoogleTranslate debug request: '.var_export($post_fields, true));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Magento/PHP');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_REFERER, Mage::helper('core/url')->getCurrentUrl());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
        $result = curl_exec($ch);

        // Detect an empty CURL response
        if(empty($result)) {
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($status_code > 200) {
                $this->apiError = Mage::helper('googletranslate')->__('Empty response: HTTP %s', $status_code);
                $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
                return false;
            } else {
                $test = curl_init();
                curl_setopt($test, CURLOPT_URL, 'https://www.googleapis.com/');
                curl_setopt($test, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($test, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($test, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($test);
                if(empty($result)) {
                    $this->apiError = Mage::helper('googletranslate')->__('Empty response: Firewall blocking: %s', curl_error($test));
                    return false;
                } else {
                    $this->apiError = Mage::helper('googletranslate')->__('Empty response: CURL-error %s', curl_error($ch));
                    return false;
                }
            }
        }

        // Detect HTML feedback
        if(preg_match('/\<\/html\>$/', $result)) {
            $this->apiError = Mage::helper('googletranslate')->__('Response is HTML, not JSON');
            $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
            return false;
        }

        // Detect non-JSON feedback
        if(!preg_match('/^\{/', $result)) {
            $this->apiError = Mage::helper('googletranslate')->__('Not a JSON response');
            $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
            return false;
        }

        // Decode the JSON-data
        $json = json_decode($result, true);
        if(isset($json['data']['translations'][0]['translatedText'])) {
            $translation = trim($json['data']['translations'][0]['translatedText']);

            // Empty translation
            if(empty($translation)) {
                $this->apiError = Mage::helper('googletranslate')->__('Empty translation');
                $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
                return false;

                // Detect whether the translation was the same or not
            } elseif($translation == $text) {
                $this->apiError = Mage::helper('googletranslate')->__('Translation resulted in same text');
                $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
                return false;

                // Send the translation
            } else {
                $this->apiTranslation = $translation;
                return $this->apiTranslation;
            }
        }

        // Detect errors and send them as feedback
        if(isset($json['error']['errors'][0]['message'])) {
            $this->apiError = Mage::helper('googletranslate')->__('GoogleTranslate message: %s', var_export($json['error']['errors'][0]['message'], true));
            $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
            return false;
        }

        $this->apiError = Mage::helper('googletranslate')->__('Unknown data');
        $this->apiError .= ' ['.Mage::helper('googletranslate')->__('From %s to %s', $fromLang, $toLang).']';
        $this->apiError .= "\n".var_export($json, true);
        return false;
    }

    public function hasApiError()
    {
        if(!empty($this->apiError)) {
            return true;
        }
        return false;
    }

    public function getApiError()
    {
        return $this->apiError;
    }

    public function getApiTranslation()
    {
        return $this->apiTranslation;
    }

    public function __($string, $variable1 = null, $variable2 = null)
    {
        return Mage::helper('googletranslate')->__($string, $variable1, $variable2);
    }
}
