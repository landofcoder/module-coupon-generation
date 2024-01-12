<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2017 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Controller\Redeem;

use Magento\Framework\App\Action\Action;
use Magento\CatalogSearch\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\Layer\Resolver;
use \Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Search\Helper\Data as SearchHelper;

class Generate extends Action
{
	CONST EMAILIDENTIFIER = 'sent_mail_with_visitor';
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Lof\CouponCode\Helper\Generator
     */
	protected $_generateHelper;

	protected $couponGenerator;

	protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;

    /**
     * @var \Lof\CouponCode\Helper\Barcode39
     */
	protected $_barcode;

    /**
     * @var \Lof\CouponCode\Helper\Data
     */
    protected $_couponHelper;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		StoreManagerInterface $storeManager,
		\Lof\CouponCode\Helper\Data $helper,
		\Lof\CouponCode\Helper\Generator $generateHelper,
		\Magento\Customer\Model\Session $customerSession,
		\Lof\CouponCode\Helper\Barcode39 $dataBarcode
	) {
		$this->_resultPageFactory  = $resultPageFactory;
		$this->_storeManager       = $storeManager;
		$this->_couponHelper       = $helper;
		$this->_customerSession    = $customerSession;
		$this->couponGenerator     = $generateHelper;
		$this->_barcode 		   = $dataBarcode;
		$this->couponGenerator->initRequireModels();
		parent::__construct($context);
	}

	/*
	* Example Url:
	*/
	public function execute()
	{
		$this->_view->loadLayout();
		if(!$this->_couponHelper->getConfig("general_settings/allow_redeem")) {
			$resultForward = $this->_resultPageFactory->create();
            return $resultForward->forward('defaultnoroute');
		}
		$only_for_customer = $this->_couponHelper->getConfig('general_settings/redeem_only_customer');
		if($only_for_customer && !$this->_customerSession->isLoggedIn())
			return;

		$barcode_folder = $this->_couponHelper->getConfig("general_settings/barcode_folder");
		$isAjax = $this->getRequest()->getParam('isAjax');
		$getBarcode = $this->getRequest()->getParam('barcode');
		$data = $this->getRequest()->getParams();
		if(!$data) {
			$customer_email = $this->getRequest()->getParam('email');
			$customer_name = $this->getRequest()->getParam('customer_name');//type, ajax, direct
			$orderid = $this->getRequest()->getParam('orderid');
			$rule_id = (int)$this->getRequest()->getParam('rule_id');//type, ajax, direct
			$customer_id = (int)$this->getRequest()->getParam('customer_id');

		} else {
			$customer_email = isset($data['email'])?$data['email']:'';//type, ajax, direct
			$customer_name = isset($data['name'])?$data['name']:'';//type, ajax, direct
			$rule_id = isset($data['rule_id'])?(int)$data['rule_id']:'';//type, ajax, direct
			$orderid = isset($data['orderid'])?$data['orderid']:'';//type, ajax, direct
			$customer_id = isset($data['customer_id'])?(int)$data['customer_id']:'';
		}

		$responseData = [];
		$track_data = [];
		$status = 'success';
		$responseData['coupon'] = '';
		$customer_email = trim($customer_email);
		if($rule_id && $customer_email) {
			$couponRuleData = $this->_couponHelper->getCouponRuleData($rule_id);
            $ruleId = (int)$couponRuleData->getRuleId();
            if($ruleId) {
            	if($customer_name) {
	            	$customer_name = $this->xss_clean($customer_name);
	            }
	            if($orderid) {
					$orderid = $this->xss_clean($orderid);
				}
				$limit_time_generated_coupon = (int)$couponRuleData->getLimitGenerated();
				$coupon_collection = $this->_objectManager->create('Lof\CouponCode\Model\Coupon')->getCollection();
                $number_generated_coupon = (int)$coupon_collection->getTotalByEmail($customer_email, $rule_id);

                if($limit_time_generated_coupon <= 0 || ($number_generated_coupon < $limit_time_generated_coupon)) {//check number coupons was generated for same email address

					$this->couponGenerator->setCustomerEmail($customer_email);
		        	$this->couponGenerator->setCustomerName($customer_name);
		        	if($this->_customerSession->isLoggedIn()) {
		        		$customer = $this->_customerSession->getCustomer();
		        		$this->couponGenerator->setCustomerId($customer->getId());
		        	}
		        	$coupon_alias = "redeem-".md5($customer_email);
		        	$this->couponGenerator->setCouponAlias($coupon_alias);

		        	$coupon_exists = false;
		        	$coupon_model = $this->_objectManager->create('Lof\CouponCode\Model\Coupon')->getCouponByAlias($coupon_alias);
		        	if($coupon_model->getId()){
		        		$coupon_exists = true;
		        	}
		        	if(!$coupon_exists){
			        	$responseData['coupon'] = $this->couponGenerator->generateCoupon($rule_id);
			        	$allow_save_log = $this->_couponHelper->getConfig('general_settings/allow_log');
			        	if($allow_save_log) {
				        	$track_link = $this->_couponHelper->getTrackLink();
				        	$track_link .= "?coupon_code=".$responseData['coupon'];
				        	$track_link .= "&email=".$customer_email;
				        	$track_link .= "&order_id=".$orderid;

				        	$responseData['track_link'] = $track_link;

				        	$log_data = [];
				        	$log_data['rule_id'] = $rule_id;
				        	$log_data['order_id'] = $orderid;
				        	$log_data['customer_id'] = $customer_id;
				        	$log_data['full_name'] = $customer_name;
				        	$log_data['generated_link'] = str_replace(array(" ",":","=","&","?"),array("+","%3A","%3D","%26","%3F"), $track_link);
				        	$log_data['email_address'] = $customer_email;
				        	$log_data['coupon_code'] = $responseData['coupon'];
				        	$log_data['discount_amount'] = $couponRuleData->getDiscountAmount();
				        	$log_data['ip_address'] = isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '';
				        	$log_data['client_info'] = isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT'] : '';

				        	//Generate barcod type 39 and return the image link
				        	if($getBarcode && $responseData['coupon']) {
				        		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				        		$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
				        		$mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
				        		$mediaPath .= $barcode_folder.DIRECTORY_SEPARATOR;
				        		$mediaPath .= $responseData['coupon'].".gif";
				        		$file_barcode_name = $barcode_folder."/".$responseData['coupon'].".gif";
				        		$barcodeHelper = $this->_barcode->initBarcode($responseData['coupon']);
				        		$barcodeHelper->draw($mediaPath);
				        		$log_data['barcode'] = str_replace(DIRECTORY_SEPARATOR, "/", $file_barcode_name);

				        		$responseData['barcode'] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
				        		$responseData['barcode'] .= $log_data['barcode'];
				        	}

				        	//insert lof data into database table ""
				        	$coupon_log_model = $this->_objectManager->create('Lof\CouponCode\Model\Log');
				        	$coupon_log_model->setData($log_data);

				        	try{
				        		$coupon_log_model->save();
				        		$responseData['generated_link'] = $log_data['generated_link'];
				        	} catch(\Exception $e){
				        		return $e;
				        	}
				        }
			        	$allow_send_email = $this->_couponHelper->getConfig('general_settings/send_email_coupon');
			        	if($allow_send_email) {
			        		$emailFrom = $this->_couponHelper->getConfig('general_settings/sender_email_identity');
		                    $emailidentifier = self::EMAILIDENTIFIER;
			        		$discount_amount_formatted = $couponRuleData->getDiscountAmount();
			        		$simple_action = $couponRuleData->getSimpleAction();
		                    if($simple_action == 'by_percent') {
		                        $discount_amount_formatted .='%';
		                    }elseif($simple_action == 'fixed'){
		                        $discount_amount_formatted ='$'.$discount_amount_formatted;
		                    }

			        		$templateVar = array(
			        					'customer_name'	=> $customer_name,
			                            'coupon_code' => $responseData['coupon'],
			                            'barcode' => $responseData['barcode'],
			                            'qrcode' => $responseData['track_link'],
			                            'rule_title' => $couponRuleData->getName(),
			                            'from_date' => $couponRuleData->getFromDate(),
			                            'to_date' => $couponRuleData->getToDate(),
			                            'simple_action' => $couponRuleData->getSimpleAction(),
			                            'discount_amount' => $couponRuleData->getDiscountAmount(),
			                            'discount_amount_formatted' => $discount_amount_formatted,
			                            'link_website' => $this->_storeManager->getStore()->getBaseUrl()
			                        );

				            $this->_couponHelper->sendMail($emailFrom,$customer_email,$emailidentifier,$templateVar);
				            $this->messageManager->addSuccess(__('A coupon code has been sent to %1.', $customer_email));
				            $responseData['message'] = __('A coupon code has been sent to %1.', $customer_email);
				        } else {
				            $this->messageManager->addSuccess(__('A coupon code has been generated.'));
				            $responseData['message'] = __("A coupon code has been generated.");
				        }
				    } else {
				    	$status = 'error';
				    	$responseData['message'] = __("A coupon was generated for the account before.");
				    }
				} else {
					$status = 'error';
				    $responseData['message'] =  __('The rule limit number coupons were generated for email %1.', $customer_email);
				}
		    } else {
		    	$status = 'error';
				$responseData['message'] = __("Rule is not exists.");
		    }
		} else {
			$status = 'error';
			$responseData['message'] = __("Missing rule id and email address.");
		}
		$responseData['status'] = $status;
		if($isAjax) {
	        $this->getResponse()->representJson(
	            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
	            );
	    }else{
	    	if($responseData['coupon']){
		    	echo '<h1>'.__("You received a coupon code:").'</h1>';
		    	echo '<p><strong>'.$responseData['coupon'].'</strong></p>';
		    } else{
		    	echo '<h1>'.__("We can not generate coupon code for you.").'</h1>';
		    }
	    }
        return;
	}

    /**
     * xss clean
     *
     * @param mixed $data
     * @return string|mixed
     */
	protected function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do
        {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        // we are done...
        return $data;
    }
}
