<?php


class BC_Search_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function suggestAction()
    {
        $this->loadLayout();
        $result = array(
            'suggest_list' => $this->getLayout()->getBlock('suggestkeyword')->toHtml(),
            'product_list' => $this->getLayout()->getBlock('resultsearch')->toHtml(),
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}