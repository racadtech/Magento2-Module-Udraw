<?php
namespace Racadtech\Udraw\Block\Adminhtml\Templates;

use Magento\Framework\View\Element\Template;
use Racadtech\Udraw\Block\Adminhtml\BaseTemplate;

class Manage extends BaseTemplate
{
    protected $templateInstance;

    public function __construct(
        Template\Context $context,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $udrawHelper, $authSession, $formKey);

        $accessKey = $this->getRequest()->getParam('access_key', '');
        $this->templateInstance = ($accessKey != "") ? $this->udrawHelper->getTemplateInstance($accessKey) : null;
    }

    public function isEditMode() : bool
    {
        return $this->templateInstance != null;
    }

    public function getTemplateInstance()
    {
        return $this->templateInstance;
    }
}
