<?php

namespace Simbeez\ImportProductPosition\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $messageManager;
 
    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;
 
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     */
    protected $fileUploader;


	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader
    ) {

        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
        $this->fileUploader = $fileUploader;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Import Product Position"));
        return $resultPage;
       
    }
   
}
?>
