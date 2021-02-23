<?php
namespace Lof\CouponCode\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
 
    const ALT_FIELD = 'name';
    private $editUrl;
 
    private $_objectManager = null;
 
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_objectManager = $objectManager;
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return mixed
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $email = isset($item['email_address'])?$item['email_address']:'';
                $coupon = isset($item['coupon_code'])?$item['coupon_code']:'';
                $order_id = isset($item['order_id'])?$item['order_id']:'';
                $name = $this->getData('name');
                $track_link = $item[$name];
                if(false === strpos($track_link, "%3Dcoupon_code")) {
                    $track_link .="?coupon_code=".$coupon;
                    $track_link .="&email=".$email;
                    if($order_id) {
                        $track_link .= "&order_id=".$order_id;
                    }
                    $track_link = str_replace(array(" ",":","=","&","?"),array("+","%3A","%3D","%26","%3F"), $track_link);
                }
                $item[$name . '_src'] = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl='.$track_link.'&choe=UTF-8';
                $item[$name . '_alt'] = __('Qr Code img');
                $item[$name . '_orig_src'] = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl='.$track_link.'&choe=UTF-8';
            }
        }
        return $dataSource;
    }
}