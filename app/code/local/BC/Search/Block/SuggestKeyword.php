<?php

class BC_Search_Block_SuggestKeyword extends Mage_Catalog_Block_Product_List
{
    protected $_suggestItems = null;

    public function isCanShowSuggestedKeywords()
    {
         if (count($this->getSuggests()) < 1) {
             return false;
         }
        return true;
    }

    /**
     * @return array
     */
    public function getSuggests()
    {

        if (null === $this->_suggestItems) {
            $suggestCollection = Mage::getResourceModel('catalogsearch/query_collection')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setQueryFilter(
                    Mage::helper('search')->getSearchedQuery()
                )
                ->setCurPage(1)
                ->setPageSize(5)
            ;
            $this->_suggestItems = $suggestCollection->getItems();
        }

        return $this->_suggestItems;
    }

    /**
     * @param string $query
     * @return string
     */
    public function getResultUrl($query)
    {
        return Mage::helper('catalogsearch')->getResultUrl($query);
    }
}