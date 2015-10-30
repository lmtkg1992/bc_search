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
        $collection = Mage::getResourceModel('eav/entity_type_collection');
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('entity_type_id'))
            ->where('entity_type_code = ?', 'catalog_product')
            ->limit(1);

        return $collection->getFirstItem()->getData('entity_type_id');

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

        $productAttributes = array();
        $attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection');
        $attributeCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'id'    => 'attribute_id',   // for applying filter to collection
                'title' => 'frontend_label', // for admin part
                'code'  => 'attribute_code', // as a tip for constructing {attribute_name}
                'type'  => 'backend_type',   // for table name
            ))
            ->where('entity_type_id = ?', $this->getEntityTypeId())
            ->where('frontend_label <> ?', "")
            ->where('find_in_set(backend_type, "text,varchar,static")')
            ->order('frontend_label')
        ;
        foreach ($attributeCollection as $attribute) {
            $productAttributes[$attribute->getData('id')] = $attribute->getData();
        }

        $productAttributeArray = array();
        foreach ($productAttributes as $attributeId => $attributeData) {
            $productAttributeArray[$attributeData['code']] = "{$attributeData['title']} ({$attributeData['code']})";
        }

        foreach($match[1] as $attributeCode) {
            if (array_key_exists($attributeCode, $productAttributeArray)) {
                $usedAttributes[] = $attributeCode;
            }
        }
        return $usedAttributes;
    }

}