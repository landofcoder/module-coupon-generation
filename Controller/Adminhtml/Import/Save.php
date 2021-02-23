<?php
/**
 * LandofCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Controller\Adminhtml\Import;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
   /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ves\Setup\Helper\Import
     */
    protected $_vesImport;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_configResource;

    /**
    * CSV Processor
    *
    * @var \Magento\Framework\File\Csv
    */
    protected $csvProcessor;

    /**
    * productFactory
    *
    * @var \Magento\Catalog\Model\ProductFactory
    */
    protected $productFactory;
    /**
     * @param \Magento\Backend\App\Action\Context                          $context
     * @param \Magento\Framework\View\Result\PageFactory                   $resultPageFactory
     * @param \Magento\Framework\Filesystem                                $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection                    $resource
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_filesystem       = $filesystem;
        $this->_storeManager     = $storeManager;
        $this->_scopeConfig      = $scopeConfig;
        $this->_configResource   = $configResource;
        $this->_resource         = $resource;
        $this->mediaConfig       = $mediaConfig;
        $this->csvProcessor = $csvProcessor;
        $this->productFactory = $productFactory;
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $filePath = $fileContent = '';
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'data_import_file']
            );

            $fileContent = '';
            if($uploader) {
                $tmpDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::TMP);
                $savePath     = $tmpDirectory->getAbsolutePath('lof/import');
                $uploader->setAllowRenameFiles(true);
                $result       = $uploader->save($savePath);
                $filePath = $tmpDirectory->getAbsolutePath('lof/import/' . $result['file']);
                $fileContent  = file_get_contents($tmpDirectory->getAbsolutePath('lof/import/' . $result['file']));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        $delimiter = $this->getRequest()->getParam('split_symbol');
        if($delimiter) {
            $importData = $this->csvProcessor->setDelimiter($delimiter)->getData($filePath);
        } else {
            $importData = $this->csvProcessor->getData($filePath);
        }
        // ----------------. Explode string to array .--------------

        foreach ($importData as &$result) {
            $result = explode(";", $result[0]);
        }

        // ----------------. End explode .--------------------------
        $store = isset($data['store_id'])? $this->_storeManager->getStore($data['store_id']) : 0;
        $connection = $this->_resource->getConnection();
        if(!empty($importData)) {
            try{
                $heading = $importData[0];
                unset($importData[0]);
                $code_index = array_search("code", $heading);
                $rule_id_index = array_search("rule_id", $heading);
                if($code_index !== false && $rule_id_index !== false) {
                    $imported_counter = 0;

                    $coupon_id_index = array_search("coupon_id", $heading);
                    $email_index = array_search("email", $heading);
                    $alias_index = array_search("alias", $heading);
                    $customer_id_index = array_search("customer_id", $heading);
                    $usage_limit_index = array_search("usage_limit", $heading);
                    $usage_per_customer_index = array_search("usage_per_customer", $heading);
                    $expiration_date_index = array_search("expiration_date", $heading);
                    $times_used_index = array_search("times_used", $heading);
                    $is_primary_index = array_search("is_primary", $heading);
                    $created_at_index = array_search("created_at", $heading);
                    $type_index = array_search("type", $heading);
                    foreach($importData as $item_data) {
                        $coupon_code = $item_data[$code_index];
                        $coupon_code = trim($coupon_code);
                        $rule_id = $item_data[$rule_id_index];
                        $rule_id = trim($rule_id);

                        $coupon_id = 0;
                        $email = "";
                        $alias = "";
                        $customer_id = 0;
                        $usage_limit = $usage_per_customer = $times_used = "";
                        $expiration_date = "";
                        $is_primary = "";
                        $created_at = date("Y-m-d H:i:s");
                        $type = 0;

                        if($coupon_id_index !== false){
                            $coupon_id = $item_data[$coupon_id_index];
                            $coupon_id = trim($coupon_id);
                        }
                        if($email_index !== false){
                            $email = $item_data[$email_index];
                            $email = trim($email);
                        }
                        if($alias_index !== false){
                            $alias = $item_data[$alias_index];
                            $alias = trim($alias);
                        }
                        if($customer_id_index !== false){
                            $customer_id = $item_data[$customer_id_index];
                            $customer_id = trim($customer_id);
                        }
                        if($usage_limit_index !== false){
                            $usage_limit = $item_data[$usage_limit_index];
                            $usage_limit = trim($usage_limit);
                        }
                        if($usage_per_customer_index !== false){
                            $usage_per_customer = $item_data[$usage_per_customer_index];
                            $usage_per_customer = trim($usage_per_customer);
                        }
                        if($times_used_index !== false){
                            $times_used = $item_data[$times_used_index];
                            $times_used = trim($times_used);
                        }
                        if($is_primary_index !== false){
                            $is_primary = $item_data[$is_primary_index];
                            $is_primary = trim($is_primary);
                        }
                        if($expiration_date_index !== false){
                            $expiration_date = $item_data[$expiration_date_index];
                            $expiration_date = trim($expiration_date);
                        }
                        if($created_at_index !== false && $item_data[$created_at_index]){
                            $created_at = $item_data[$created_at_index];
                            $created_at = trim($created_at);
                        }
                        if($type_index !== false){
                            $type = $item_data[$type_index];
                            $type = trim($type);
                            $type = (int)$type;
                        }
                        if($coupon_code && $rule_id) {
                            $coupon_model = $this->_objectManager->create('Lof\CouponCode\Model\Coupon');
                            $rule_model = $this->_objectManager->create('Lof\CouponCode\Model\ResourceModel\Rule');
                            if($rule_model->lookupRuleByid($rule_id)){
                                if($alias){
                                    $coupon_model->getCouponByAlias($alias);
                                }
                                $mage_coupon_data = [
                                                        "code" => $coupon_code,
                                                        "rule_id" => $rule_id,
                                                        "usage_limit" => $usage_limit,
                                                        "usage_per_customer" => $usage_per_customer,
                                                        "expiration_date" => $expiration_date,
                                                        "times_used" => $times_used,
                                                        "is_primary" => $is_primary,
                                                        "created_at" => $created_at,
                                                        "type" => $type
                                                    ];
                                $mage_coupon_model = $this->_objectManager->create('Magento\SalesRule\Model\Coupon');

                                if($coupon_id){
                                   $mage_coupon_model->load((int)$coupon_id);
                                }
                                if(!$mage_coupon_model->getId() && $coupon_model->getCouponId()){
                                    $coupon_id = $coupon_model->getCouponId();
                                    $mage_coupon_model->load($coupon_id);
                                }
                                $mage_coupon_model->setData($mage_coupon_data);
                                $mage_coupon_model->save();

                                if(!$coupon_id && $mage_coupon_model->getId()){
                                    $coupon_id = $mage_coupon_model->getId();
                                }

                                $data = [
                                        "code" => $coupon_code,
                                        "coupon_id" => (int)$coupon_id,
                                        "rule_id" => (int)$rule_id,
                                        "email" => $email,
                                        "alias" => $alias,
                                        "customer_id" => (int)$customer_id
                                    ];
                                $coupon_model->setData($data);
                                $coupon_model->save();
                            }

                        }
                        $imported_counter++;
                    }

                    if($imported_counter > 0)
                        $this->messageManager->addSuccess(__("Import successfully"));
                } else {
                    $this->messageManager->addError(__("Required there columns: code and rule_id"));
                }

            }catch(\Exception $e){
                $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_CouponCode::import_coupon');
    }
}
