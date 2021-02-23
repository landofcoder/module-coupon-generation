<?php
/**
 * @category Magento 2 Module
 * @package  Overdosedigital\Frontendflow
 * @author   Don Nuwinda
 */
namespace Lof\CouponCode\Controller\Adminhtml\Rule;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Lof\CouponCode\Model\ResourceModel\Rule\CollectionFactory;

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
        foreach ($searchResult->getItems() as $document) {
            if( in_array( $document->getId(), $ids ) ) {
                $time_used = $document['times_used'];
                $uses_per_coupon = $document['uses_per_coupon'];

                $usage_rate = ((int)$document['uses_per_coupon'] != 0)? (float)($document['times_used']/$document['uses_per_coupon'])*100 : 0;
//                $fieldName = $this->getData('name');
                $document['usage_rate'] = round($usage_rate) .'%';

                $document['times_used'] = $time_used.'/'.$uses_per_coupon;
                $this->metadataProvider->convertDate($document, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($document, $fields, $options));
            }
        }
        $stream->unlock();
        $stream->close();
        return $this->fileFactory->create('export.csv', [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ], 'var');
    }
}