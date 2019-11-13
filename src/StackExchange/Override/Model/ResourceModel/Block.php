<?php
/*
 * Rewritten Resource Model Block Class to fix the issue 
 * "A block identifier with the same properties already exists in the selected store."
 * 
 *  
 * @author Jerome Dennis <haijerome@gmail.com>
 * 
 */
namespace StackExchange\Override\Model\ResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * CMS block model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Block extends \Magento\Cms\Model\ResourceModel\Block
{
    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param AbstractModel $object
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsUniqueBlockToStores(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(BlockInterface::class);
        $linkField = $entityMetadata->getLinkField();
        
        $stores = (array)$object->getData('store_id');
        
        $isDefaultStore = $this->_storeManager->isSingleStoreMode()
            || array_search(Store::DEFAULT_STORE_ID, $stores) !== false;
        
//        if (!$isDefaultStore) {
//            $stores[] = Store::DEFAULT_STORE_ID;
//        }
        
        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->join(
                ['cbs' => $this->getTable('cms_block_store')],
                'cb.' . $linkField . ' = cbs.' . $linkField,
                []
            )
            ->where('cb.identifier = ?  ', $object->getData('identifier'));
        
            $select->where('cbs.store_id IN (?)', $stores);
        
        if ($object->getId()) {
            $select->where('cb.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
        }
        
//        echo '<br/><br/>Default store id : ' . Store::DEFAULT_STORE_ID;        
//        echo '<br/><br/>SQL : '.$select->__toString() . PHP_EOL; exit;
        
        if ($this->getConnection()->fetchRow($select)) {
//            echo 'RETURN FALSE' . PHP_EOL;exit;
            return false;
        }
        
        return true;
    }
}