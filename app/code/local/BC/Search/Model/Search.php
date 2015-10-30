<?php
 class BC_Search_Model_Search extends Varien_Object{
     public function search($searchedQuery, $storeId = null){

         if (is_null($storeId)) {
             $storeId = Mage::app()->getStore()->getId();
         }
         if (is_null($searchedQuery) || is_null($storeId)) {
             return null;
         }

         $productCollection = null;

         /*we will search product base on some specific attribute*/
         $searchableAttributes = explode(',', Mage::helper('search/config')->getInterfaceSearchableAttributes());


         $productIds = array();
         foreach ($searchableAttributes as $attributeId) {
             if (Mage::helper('search')->isFulltext($attributeId)) {
                 $productIds = array_merge($productIds, $this->searchProductsFulltext($searchedQuery, $storeId));
             } else {
                 $productIds = array_merge($productIds, $this->searchProducts($storeId));
             }
         }


         if (!count($productIds)) {
             return null;
         }

         $productCollection = $this->_prepareCollection();

         $productCollection->addFilterByIds($productIds)
                             ->addStoreFilter($storeId)
                             ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                             ->setVisibility(array(
                                 Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                                 Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                             ));

         if (is_null($productCollection)) {
             return null;
         }


         $productCollection = $this->_postProcessCollection($productCollection);

         $productCollection->setOrder('name', Varien_Data_Collection::SORT_ORDER_ASC);
         $productCollection->setPageSize(Mage::helper('search/config')->getSettingMaxProducts());/*hardcode for display 4 product,addconfig later*/

         return $productCollection;

     }

     protected function _prepareCollection()
     {
         $collection = Mage::getModel('search/product');

         return $collection;
     }

     protected function _postProcessCollection($productCollection)
     {
         $productCollection->addAttributeToSelect('*')
                         ->addMinimalPrice()
                         ->addFinalPrice()
                         ->groupByAttribute('entity_id');

         return $productCollection;
     }

     public function searchProductsFulltext($searchedQuery, $storeId)
     {
         $resource = Mage::getSingleton('core/resource');
         $connection = $resource->getConnection('core_read');
         $select = $connection->select();
         $fullTextTable = $resource->getTableName('catalogsearch/fulltext');
         $select->from($fullTextTable, 'product_id')
             ->where('store_id = ?',$storeId)
             ->where('data_index LIKE ?', "%{$searchedQuery}%");
         $result = $connection->fetchCol($select);
         return $result;
     }

     public function searchProducts($storeId){


         $ids = array();
         $resource = Mage::getSingleton('core/resource');
         $db = $resource->getConnection('core_read');

         $searchedWords = Mage::helper('search')->getSearchedWords();
         $attributes = $this->_getSearchableAttributesTypes();

         if (count($attributes) == 0) {
             return $ids;
         }
         $entityTypeId = Mage::helper('search')->getEntityTypeId();

         foreach ($attributes as $tableName) {
             if ($tableName != 'static') {
                 $select = $db->select();

                 if ($tableName == 'int') {
                     $eaov = $resource->getTableName('eav/attribute_option_value');
                     $cpei = $resource->getTableName('catalog/product') . '_' . $tableName;

                     $select->from(array('cpei' => $cpei), array('cpei.entity_id'))
                         ->join(array('eaov' => $eaov), 'cpei.`value` = eaov.option_id', array())
                         ->where('cpei.entity_type_id=?', $entityTypeId)
                         ->where('cpei.store_id=0 OR cpei.store_id=?', $storeId)
                         ->where('cpei.attribute_id IN (' . implode(',', array_keys($attributes)) . ')');

                     foreach ($searchedWords as $value) {
                         $select ->where('eaov.`value` LIKE "%' . addslashes($value) . '%"');
                     }
                 } else {
                     $select
                         ->distinct()
                         ->from($resource->getTableName('catalog/product') . '_' . $tableName, 'entity_id')
                         ->where('entity_type_id=?', Mage::helper('search')->getEntityTypeId())
                         ->where('store_id=0 OR store_id=?', $storeId)
                         ->where('attribute_id IN (' . implode(',', array_keys($attributes)) . ')');
                     foreach ($searchedWords as $value) {
                         $select->where('`value` LIKE "%' . addslashes($value) . '%"');
                     }
                 }
                 $ids = array_merge($ids, $db->fetchCol($select));
             }
             if ($tableName == 'static') {
                 $select = $db->select();

                 $select
                     ->distinct()
                     ->from($resource->getTableName('catalog/product'), 'entity_id')
                     ->where('entity_type_id=?', $entityTypeId);

                 foreach ($searchedWords as $value) {
                     $select->where('`sku` LIKE "%' . addslashes($value) . '%"');
                 }
                 $ids = array_merge($ids, $db->fetchCol($select));
             }
         }

         return array_unique($ids);

     }

     protected function _getSearchableAttributesTypes()
     {

         $attributes = array();
         $searchableAttributes = explode(',', Mage::helper('search/config')->getInterfaceSearchableAttributes());

         if (count($searchableAttributes) !== 0) {
             foreach ($searchableAttributes as $attributeId) {

                 $attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage::helper('search')->getEntityTypeId(),$attributeId);
                 if ($attribute->getId()) {
                     $attributes[$attribute->getId()] = $attribute->getBackendType();
                 }
             }
         }

         return $attributes;
     }

 }