<?php
namespace Simbeez\ImportProductPosition\Model\Import;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class PositionImport extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const SKU = 'sku';
    const CATEGORY = 'category';
    const POSITION = 'position';
    const TABLE_Entity = 'catalog_category_product';
    const DELIMITER_CATEGORY = '/';
    const CATEGORY_SEPERATOR = '|';

    protected $_permanentAttributes = [
        self::SKU,
        self::CATEGORY,
        self::POSITION
    ];
    protected $needColumnCheck = true;
    protected $validColumnNames = [
        self::SKU,
        self::CATEGORY,
        self::POSITION
    ];
    protected $logInHistory = true;
    protected $_validators = [];
    protected $_connection;
    protected $_resource;
    protected $categories = [];
    protected $categoryColFactory;
    protected $productFactory;
    protected $skus = [];
    protected $countItemsUpdated = 0;
    protected $rootCategoryId = 1;


    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->categoryColFactory = $categoryColFactory;
        $this->productFactory = $productFactory;
        $this->initMessageTemplates();
        $this->getRootCategoryId();
        // $this->initCategories();
        // $this->initProducts();
    }
    /**
     * Initialize categories
     *
     * @return $this
     */
    protected function initCategories()
    {
        if (empty($this->categories)) {
            $collection = $this->categoryColFactory->create();
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            foreach ($collection as $category) {
                $structure = explode(self::DELIMITER_CATEGORY, $category->getPath());
                $pathSize = count($structure);

                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $name = $collection->getItemById((int)$structure[$i])->getName();
                        $path[] = $this->quoteDelimiter($name);
                    }
                    /** @var string $index */
                    $index = $this->standardizeString(
                        implode(self::DELIMITER_CATEGORY, $path)
                    );
                    $this->categories[$index] = $category->getId();
                }
            }
        }
        return $this;
    }
    /**
     * Quoting delimiter character in string.
     *
     * @param string $string
     * @return string
     */
    private function quoteDelimiter($string)
    {
        return str_replace(self::DELIMITER_CATEGORY, '\\' . self::DELIMITER_CATEGORY, $string);
    }
    /**
     * Standardize a string.
     *
     * @param string $string
     * @return string
     */
    private function standardizeString($string)
    {
        return mb_strtolower($string);
    }
    /**
     * Initialize Products
     *
     * @return $this
     */
    protected function initProducts()
    {
        $productcollection = $this->productFactory->create()->getCollection()->addAttributeToSelect('sku')->addAttributeToSelect('entity_id')->addAttributeToSelect('category_ids');
        foreach ($productcollection as $info) {
            $sku = $info->getSku();
            $this->skus[$sku] = [
                'entity_id' => $info->getEntityId(),
                'category_ids' => $info->getCategoryIds()
            ];
        }
        return $this;
    }

    protected function getRootCategoryId() {
        $collection = $this->categoryColFactory->create();
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('parent_id',0);
        $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $this->rootCategoryId = $collection->getFirstItem()->getId();
    }

    /**
     * @param $category string
     */
    protected function initCategoryData($category) {
        $explodedCategory = explode(self::CATEGORY_SEPERATOR,$category);
        foreach($explodedCategory as $category){
            $categoriesName = explode(self::DELIMITER_CATEGORY,$category);
            $categoryPath = [$this->rootCategoryId];
            foreach($categoriesName as $categoryName) {
                $collection = $this->categoryColFactory->create();
                $collection->addAttributeToSelect('name')
                    ->addAttributeToSelect('url_key')
                    ->addAttributeToSelect('url_path')
                    ->addAttributeToFilter('name',['like' => $categoryName]);
                $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
                $categoryPath[] = $collection->getFirstItem()->getId();
            }
            $categoryPath = implode(self::DELIMITER_CATEGORY, $categoryPath);
            $collection = $this->categoryColFactory->create();
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path')
                ->addAttributeToFilter('path',['like' => $categoryPath]);
            $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

            $this->categories[$this->standardizeString($category)] = $collection->getFirstItem()->getId();
        }
        return $this;
    }

    /**
     * @param $sku string
     */
    protected function initProductData($sku) {
        $productcollection = $this->productFactory->create()->getCollection()->addAttributeToSelect('sku')->addAttributeToSelect('entity_id')->addAttributeToSelect('category_ids')->addFieldToFilter('sku',trim($sku));
        foreach ($productcollection as $info) {
            $sku = $info->getSku();
            $this->skus[$sku] = [
                'entity_id' => $info->getEntityId(),
                'category_ids' => $info->getCategoryIds()
            ];
        }
        return $this;
    }

    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }
    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'product_position';
    }
    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $sku = $rowData['sku'] ?? '';
        $category = $rowData['category'] ?? '';
        $position = $rowData['position'] ?? '';

        if (!$sku) {
            $this->addRowError('SkuIsRequired', $rowNum);
        }
        if(!$category){
            $this->addRowError('CategoryIsRequired',$rowNum);
        }
        if(!$position){
            $this->addRowError('PositionIsRequired',$rowNum);
        }

        // $this->initCategoryData($category);
        $this->initCategories();
        $this->initProductData($sku);

        $explodedCategory = explode(self::CATEGORY_SEPERATOR,$category);

        foreach($explodedCategory as $category){

            $standardizestring = $this->standardizeString(implode(self::DELIMITER_CATEGORY, explode(self::DELIMITER_CATEGORY,$category)));

            if(!isset($this->categories[$standardizestring])){
                $this->addRowError('CategoryNotExists',$rowNum);
            }
            if(isset($this->skus[$sku]) && isset($this->categories[$standardizestring])){
                if(!in_array($this->categories[$standardizestring],$this->skus[$sku]['category_ids'])){
                    $this->addRowError('ProductNotExistsinCategory',$rowNum);
                }
            }

        }

        if(!isset($this->skus[$sku])){
            $this->addRowError('SkuNotExists',$rowNum);
        }
        
        if(!is_numeric($position)){
            $this->addRowError('InvalidPosition',$rowNum);
        }
    
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
    
        $this->_validatedRows[$rowNum] = true;
    
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }
    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'SkuIsRequired',
            __('The sku cannot be empty.')
        );
        $this->addMessageTemplate(
            'CategoryIsRequired',
            __('The category cannot be empty.')
        );
        $this->addMessageTemplate(
            'PositionIsRequired',
            __('The position cannot be empty.')
        );
        $this->addMessageTemplate(
            'CategoryNotExists',
            __('The category does not exist.')
        );
        $this->addMessageTemplate(
            'SkuNotExists',
            __('The sku does not exist.')
        );
        $this->addMessageTemplate(
            'ProductNotExistsinCategory',
            __('This sku does not exist in this category.')
        );
        $this->addMessageTemplate(
            'InvalidPosition',
            __('Position is invalid.')
        );
    }
    /**
     * Create Advanced position data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        $this->saveEntity();
        return true;
    }
    /**
     * Save Position
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Save position
     *
     * @return $this
     */
    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $sku = $rowData[self::SKU];
                $category = $rowData[self::CATEGORY];
                $position = $rowData[self::POSITION];
                
                if($sku){
                    $product_id = $this->skus[$sku]['entity_id'];
                }
                if($category){
                    $explodedCategory = explode(self::CATEGORY_SEPERATOR,$category);

                    foreach($explodedCategory as $category){
                        $standardizestring = $this->standardizeString(implode(self::DELIMITER_CATEGORY, explode(self::DELIMITER_CATEGORY,$category)));
                        $category_id = $this->categories[$standardizestring];
                        $entityList = [];
                        if(isset($product_id) && isset($category_id) && isset($position)){
                            $entityList[self::SKU][$category_id] = [
                                'product_id' => $product_id,
                                'category_id' => $category_id,
                                'position' => $position,
                            ];
                            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                                $this->saveEntityFinish($entityList, self::TABLE_Entity);
                            }
                        }
                    }
                }
                // if(isset($product_id) && isset($category_id) && isset($position)){
                //     $entityList[self::SKU][] = [
                //         'product_id' => $product_id,
                //         'category_id' => $category_id,
                //         'position' => $position,
                //     ];
                // }

            }
            // if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
            //     $this->saveEntityFinish($entityList, self::TABLE_Entity);
            // }
        }
        
        return $this;
    }
    /**
     * Save position.
     *
     * @param array $positionData
     * @param string $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table)
    {
    if ($entityData) {
        $tableName = $this->_resource->getTableName($table);
        $entityIn = [];
        foreach ($entityData as $id => $entityRows) {
                foreach ($entityRows as $row) {
                    $entityIn[] = $row;
                }
        }
        if ($entityIn) {
            $this->_connection->insertOnDuplicate($tableName, $entityIn,[
                'product_id',
                'category_id',
                'position',
        ]);
        $this->countItemsUpdated += count($entityIn);
        // $this->updateItemsCounterStats([], $entityIn, []);
        }
    }
    return $this;
    }
}