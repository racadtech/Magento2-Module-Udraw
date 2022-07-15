<?php

namespace Racadtech\Udraw\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class PriceMatrixActions extends Column
{
    /** Url path */
    const PRICEMATRIX_URL_PATH_EDIT = 'udraw/pricematrix/manage';
    const PRICEMATRIX_URL_PATH_DELETE = 'udraw/pricematrix/delete';

    /** @var UrlBuilder */
    protected UrlBuilder $actionUrlBuilder;

    /** @var UrlInterface */
    protected UrlInterface $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::PRICEMATRIX_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['udraw_pricematrix_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['access_key' => $item['access_key']]),
                        'label' => __('Edit')
                    ];
                    /*
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::PRICEMATRIX_URL_PATH_DELETE, ['udraw_pricematrix_id' => $item['udraw_pricematrix_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.name }'),
                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.name } record?')
                        ]
                    ];*/
                }
            }
        }

        return $dataSource;
    }
}
