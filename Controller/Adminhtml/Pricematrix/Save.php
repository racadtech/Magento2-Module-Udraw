<?php

namespace Racadtech\Udraw\Controller\Adminhtml\Pricematrix;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Racadtech\Udraw\Helper\Udraw
     */
    protected $udrawHelper;

    /**
     * @var \Racadtech\Udraw\Model\PricematrixFactory
     */
    protected $pricematrixFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Racadtech\Udraw\Model\PricematrixFactory $pricematrixFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->pricematrixFactory = $pricematrixFactory;
        $this->authSession = $authSession;
        $this->logger = $logger;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        if (array_key_exists('udraw_pricematrix_access_key', $postData)) {
            $accessKey = $postData['udraw_pricematrix_access_key'];
        } else {
            $accessKey = "";
        }

        $priceMatrixInstance = $this->udrawHelper->getPricematrixInstance($accessKey);
        if ($priceMatrixInstance != null) {
            // Updating existing Price Matrix
            $priceMatrixInstance->setName($postData['udraw_pricematrix_name']);
            $priceMatrixInstance->setPriceData($postData['udraw_pricematrix_price_data']);
            $isUpdated = $priceMatrixInstance->save();

            if ($isUpdated) {
                $this->messageManager->addSuccessMessage('Price Matrix Updated.');
            } else {
                $this->messageManager->addErrorMessage('Failed to Update Price Matrix');
            }
        } else {
            // Creating new Price Matrix.
            $priceMatrix = $this->pricematrixFactory->create();
            $accessKey = uniqid();
            $priceMatrix->addData([
                'name' => $postData['udraw_pricematrix_name'],
                'price_data' => $postData['udraw_pricematrix_price_data'],
                'access_key' => $accessKey,
                'measurement' => 'in',
                'create_user' => $this->authSession->getUser()->getUserName()
            ]);

            $isCreated = $priceMatrix->save();
            if ($isCreated) {
                $this->messageManager->addSuccessMessage('Price Matrix Created.');
            } else {
                $this->messageManager->addErrorMessage('Failed to create Price Matrix.');
            }
        }
        return $this->resultRedirectFactory->create()->setPath('udraw/pricematrix/manage', ['access_key' => $accessKey]);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Racadtech_Udraw::pricematrix_create');
    }
}
