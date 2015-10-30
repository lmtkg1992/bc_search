<?php
class BC_Search_Helper_Config extends Mage_Core_Helper_Abstract{

    const CONFIG_PATH_APPEARANCE_ITEM_TEMPLATE = "search/appearance/item_template";
    const CONFIG_PATH_APPEARANCE_SHOW_MAX_PRODUCT = "search/appearance/show_max_product";
    const CONFIG_PATH_APPEARANCE_SHOW_BUTTION_ALL = "search/appearance/show_buttion_all";
    const CONFIG_PATH_APPEARANCE_THUMBNAIL_SIZE = "search/appearance/thumbnail_size";
    const CONFIG_PATH_APPEARANCE_PRELOADER_IMAGE= "search/appearance/preloader_image";

    public function getInterfaceSearchableAttributes(){
        return 'name,description,meta_description,meta_keyword';
    }
    public function getInterfaceItemTemplate($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_APPEARANCE_ITEM_TEMPLATE, $store);
    }

    public function getSettingMaxProducts($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_APPEARANCE_SHOW_MAX_PRODUCT, $store);
    }

    public function getSettingThumbnailSize($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_APPEARANCE_THUMBNAIL_SIZE, $store);
    }

    public function getInterfaceShowAllResultsButton($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_APPEARANCE_SHOW_BUTTION_ALL, $store);
    }

    public function getInterfacePreloaderImageFilename($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_APPEARANCE_PRELOADER_IMAGE, $store);
    }

    /**
     * @return string
     */
    public function getInterfacePreloaderImageUrl($store = null)
    {
        $preloaderImageFilename = $this->getInterfacePreloaderImageFilename($store);

        if($preloaderImageFilename) {
            $storeMediaUrl = Mage::app()->getStore($store)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
            return $storeMediaUrl . 'bc_search/' . $preloaderImageFilename;
        } else {
            return Mage::getDesign()->getSkinUrl('images/bc_search/preloader.gif');
        }
    }

}