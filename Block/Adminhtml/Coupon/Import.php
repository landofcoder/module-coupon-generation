<?php
/**
 * LandofCoder
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
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2017 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Block\Adminhtml\Coupon;

class Import extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_urlBuilder;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context  
     * @param \Magento\Framework\Registry           $registry 
     * @param array                                 $data     
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url $url,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_urlBuilder = $url;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'coupon_id';
        $this->_blockGroup = 'Lof_CouponCode';
        $this->_controller = 'adminhtml_import';

        parent::_construct();

        if ($this->_isAllowedAction('Lof_CouponCode::coupon_import')) {
            $this->buttonList->update('save', 'label', __('Save Coupon Code'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );

        }else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('lofcouponcode_coupon')->getId()) {
            return __("Edit Form '%1'", $this->escapeHtml($this->_coreRegistry->registry('lofcouponcode_coupon')->getName()));
        } else {
            return __('New Form');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }


    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    
//         protected function _afterToHtml($html)
//     {
//         return parent::_afterToHtml($html . $this->_getJsInitScripts());
//     }

//     protected function _getJsInitScripts()
//     {
//         $refreshUrls = [];
//         $model = $this->_coreRegistry->registry('loffollowupemail_email');
//         $dataModel = $model->getData();
//         if($dataModel) {
//             isset($dataModel['email_name'])?$data['email_name'] = $dataModel['email_name']:$data['email_name'] ='';
//             $data['event_type'] = $dataModel['event_type'];
//             isset($dataModel['subject'])?$data['subject'] = $dataModel['subject']:$data['subject'] ='';
//             isset($dataModel['subject'])?$data['content'] = $dataModel['content']:$data['content']  ='';
//         }
//         $data_send = json_encode($data);
//         $editUrl = $this->getUrl(
//                                 'loffollowupemail/email/sendtestemail' 
//                             );
//         return <<<HTML
//     <script>
//         require(['jquery'], function(jQuery){
//             jQuery(document).ready(function() {
//                 jQuery('#sendtest_button').click(function(){
//                  jQuery.post(
//                    '{$editUrl}', 
//                    {
//                       "data" : {$data_send}

//                   }).done(function(data) 
//                   {  
//                     if(data === 'error'){
//                          jQuery('.followupemail-message').html('Error');  
//                     }
//                      jQuery('.followupemail-message').html('Success !!!');    
//                }).fail(function() { 
                    
//                }).always(function () { 
//                }); 

//            });
//             });
//         });
//     </script>
// HTML;
//     } 
    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('couponcode/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
        require([
        'jquery',
        'mage/backend/form'
        ], function(){
            jQuery('#duplicate').click(function(){
                var actionUrl = jQuery('#edit_form').attr('action') + 'duplicate/1';
                jQuery('#edit_form').attr('action', actionUrl);
                jQuery('#edit_form').submit();
            });

            function toggleEditor() {
                if (tinyMCE.getInstanceById('before_form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'before_form_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'before_form_content');
                }
            };
        });";
        return parent::_prepareLayout();
    }
}
