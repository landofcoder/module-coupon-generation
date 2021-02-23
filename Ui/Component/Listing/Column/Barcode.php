<?php
namespace Lof\CouponCode\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Barcode extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'barcode';
 
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
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $mediaUrl =  $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $barcode = $item[$name];
                if(!$barcode) {
                    $barcode = "lof_couponcode/no_image_available.jpg";
                }
                $barcode_image_url = $mediaUrl.$barcode;
                $item[$name . '_src'] = $barcode_image_url;
                $item[$name . '_alt'] = __('Barcode img');
                $item[$name . '_orig_src'] = $barcode_image_url;
            }
        }
        return $dataSource;
    }
}