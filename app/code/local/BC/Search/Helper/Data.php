<?php
class Bc_Search_Helper_Data extends Mage_Core_Helper_Abstract{
    public function getSearchedQuery()
    {
        $searchQuery =  Mage::app()->getRequest()->getParam('q');
        if (is_null($searchQuery)) {
            $searchQuery = '';
        }
        return htmlspecialchars_decode(Mage::helper('core')->escapeHtml($searchQuery));
    }

    public function getEntityTypeId()
    {
        return Mage::getSingleton('search/source_product_attribute')->getEntityTypeId();
    }

    public function getSearchedWords()
    {
        $searchedQuery = $this->getSearchedQuery();
        $searchedWords = explode(' ', trim($searchedQuery));
        for ($i = 0; $i < count($searchedWords); $i++) {
            if (strlen($searchedWords[$i]) < 2 || preg_match('(:)', $searchedWords[$i])) {
                unset($searchedWords[$i]);
            }
        }
        return $searchedWords;
    }

    public function isFulltext($attributeId)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        if (($attribute->getData('is_searchable') == 1) && ($attribute->getData('frontend_input') == 'textarea')) {
            return true;
        }
        return false;
    }

    public function getUsedAttributes()
    {
        $usedAttributes = array();
        $itemPattern = Mage::helper('search/config')->getInterfaceItemTemplate();
        $pattern = '/{([^}]*)}/si';
        preg_match_all($pattern, $itemPattern, $match);

        $attributeModel = Mage::getSingleton('search/source_product_attribute');
        $attributesArray = $attributeModel->toArray();
        foreach($match[1] as $attributeCode) {
            if (array_key_exists($attributeCode, $attributesArray)) {
                $usedAttributes[] = $attributeCode;
            }
        }
        return $usedAttributes;
    }

}