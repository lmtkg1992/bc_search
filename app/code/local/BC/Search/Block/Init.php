<?php
class BC_Search_Block_Init extends Mage_Core_Block_Template
{
    /**
     * @return string
     */
    public function getQueryParamKey()
    {
        $queryParamKey = Mage::helper("catalogSearch")->getQueryParamName();
        return Zend_Json::encode($queryParamKey);
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        $searchUrl = Mage::app()->getStore()->getUrl('search/ajax/suggest');
        if(Mage::app()->getStore()->isCurrentlySecure()) {
            $searchUrl = str_replace('http://', 'https://', $searchUrl);
        }
        return Zend_Json::encode($searchUrl);
    }

    /**
     * @return string
     */
    public function getQueryDelay()
    {
        $queryDelayInSec = 10;
        return Zend_Json::encode($queryDelayInSec);
    }

    /**
     * @return string
     */
    public function getIsOpenInNewWindow()
    {
        $isOpenInNewWindow = true; 
        return Zend_Json::encode($isOpenInNewWindow);
    }

    /**
     * @return string
     */
    public function getIndicatorImage()
    {
        $preloaderImage = Mage::helper("search/config")->getInterfacePreloaderImageUrl();
        return Zend_Json::encode($preloaderImage);
    }

    /**
     * @return string
     */
    public function getUpdateChoicesElementHeader()
    {
        $headerHtml = "Most relevant matches are shown. Click Search for more items";
        return is_null($headerHtml)?"":$headerHtml;
    }

    /**
     * @return string
     */
    public function getUpdateChoicesElementFooter()
    {
        $footerHtml = "";
        return is_null($footerHtml)?"":$footerHtml;
    }
}