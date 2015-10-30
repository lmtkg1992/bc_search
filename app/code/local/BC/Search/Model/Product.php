<?php

class BC_Search_Model_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    public function addFilterByIds($ids)
    {
        if ($ids) {
            $whereString = '(e.entity_id IN (';
            $whereString .= implode(',', $ids);
            $whereString .= '))';
            $this->getSelect()->where($whereString);
        }
        return $this;
    }

}