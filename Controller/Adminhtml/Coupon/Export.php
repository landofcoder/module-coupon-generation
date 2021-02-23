<?php
/**
 * @category Magento 2 Module
 * @package  Overdosedigital\Frontendflow
 * @author   Don Nuwinda
 */
namespace Lof\CouponCode\Controller\Adminhtml\Coupon;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Lof\CouponCode\Model\ResourceModel\Coupon\CollectionFactory;

class Export extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;
    /**
     * @var WriteInterface
     */
    protected $directory;
    /**
     * @var ConvertToCsv
     */
    protected $converter;
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    protected $resources;
    protected $_connection;
    protected $collectionFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        Filter $filter,
        Filesystem $filesystem,
        ConvertToCsv $converter,
        FileFactory $fileFactory,
        \Magento\Ui\Model\Export\MetadataProvider $metadataProvider,
        \Lof\CouponCode\Model\ResourceModel\RuleFactory $resource,
        CollectionFactory $collectionFactory
    ) {
        $this->resources = $resource;
        $this->filter = $filter;
        $this->_connection = $this->resources->create()->getConnection();
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * export.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection();
        $component = $this->filter->getComponent();
        $this->filter->prepareComponent($component);
        $dataProvider = $component->getContext()->getDataProvider();
        $dataProvider->setLimit(0, false);
        $ids = [];

        foreach ($collection as $document) {
            $ids[] = (int)$document->getId();
        }

        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();
        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        foreach ($searchResult->getItems() as $item) {
            if( in_array( $item->getId(), $ids ) ) {

                $rule_name = $item['name'];
                $item['rule_name'] = $rule_name;

                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
        }
        $stream->unlock();
        $stream->close();
        return $this->fileFactory->create('couponcode.csv', [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ], 'var');
    }
}