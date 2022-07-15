<?php

namespace Racadtech\Udraw\Block\Product;

use Magento\Framework\App\Http\Context as HttpContext;

class View extends \Magento\Catalog\Block\Product\View
{
    protected \Racadtech\Udraw\Helper\Udraw $udrawHelper;
    protected HttpContext $httpContext;
    protected \Magento\Framework\Serialize\Serializer\Json $serializer;
    protected \Magento\Checkout\Model\Session $checkoutSession;
    protected $cartItem;
    protected $requestParameters;
    protected $locale;

    public function __construct(
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        HttpContext $httpContext,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Locale\Resolver $locale,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->serializer = $serializer;
        $this->checkoutSession = $checkoutSession;
        $this->udrawHelper = $udrawHelper;
        $this->locale = $locale;

        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );

        $this->requestParameters = $this->_request->getParams();
        $this->cartItem = (key_exists('id', $this->requestParameters)) ?
            $this->udrawHelper->getCartItemById($this->requestParameters['id']) : null;
    }

    public function containsUdrawTemplate() : bool
    {
        return ($this->getTemplateInstance() != null);
    }

    public function containsPriceMatrix(): bool
    {
        return ($this->getProduct()->getData('udraw_linked_pricematrix') != null);
    }

    public function isUpdate() : bool
    {
        return ($this->cartItem != null);
    }

    public function displayDesignerFirst() : bool
    {
        $designerParam = (key_exists('designer', $this->requestParameters)) ? $this->requestParameters['designer'] : 'false';
        return ($designerParam == 'true');
    }

    public function getStoreLocale() : string
    {
        return strtolower($this->locale->getLocale());
    }

    public function getPriceMatrixInstance()
    {
        $priceMatrixAccessKey = $this->getProduct()->getData('udraw_linked_pricematrix');
        if ($priceMatrixAccessKey != null) {
            return $this->udrawHelper->getPricematrixInstance($priceMatrixAccessKey);
        }
        return null;
    }

    public function getTemplateInstance()
    {
        $templateAccessKey = $this->getProduct()->getData('udraw_linked_template');
        if ($templateAccessKey != null) {
            return $this->udrawHelper->getTemplateInstance($templateAccessKey);
        }
        return null;
    }

    public function getGoSendExInstance(): array
    {
        $isEnabled = $this->getProduct()->getData('udraw_enable_gosendex_upload');
        $goSendExInstance = [
            "enabled" => $isEnabled == "Enabled",
            "apikey" => $this->udrawHelper->getUdrawSettingValue('gosendex_api_key'),
            "domain" => $this->udrawHelper->getUdrawSettingValue('gosendex_domain')
        ];

        // We need to create this check as users will be able to "Enable" GoSendEx on product page.
        //
        // Check to see if apikey and domain are both set. If not, we will force enabled to false as these are required
        // for GoSendEx to work properly.
        if ($goSendExInstance["apikey"] == null || $goSendExInstance["domain"] == null) {
            $goSendExInstance["enabled"] = false;
        }
        return $goSendExInstance;
    }

    public function getUdrawTemplateDesignKey() : string
    {
        if ($this->isUpdate()) {
            $udrawDesign = $this->udrawHelper->getUdrawDesignFromCartQuoteItem($this->cartItem);
            if ($udrawDesign != null) {
                return $udrawDesign->designKey;
            }
        }

        return $this->getTemplateInstance()->getAccessKey();
    }

    public function getCartUdrawData(): ?array
    {
        if ($this->isUpdate()) {
            return $this->udrawHelper->getUdrawDataFromCartQuoteItem($this->cartItem);
        }
        return null;
    }

    public function getCartPriceMatrixSaved()
    {
        $cartUdrawData = $this->getCartUdrawData();
        if ($cartUdrawData != null) {
            if (key_exists('udraw_pricematrix_selected_saved', $cartUdrawData)) {
                return $this->serializer->serialize(base64_decode($cartUdrawData['udraw_pricematrix_selected_saved']));
            }
        }
        return null;
    }

    public function getCartPriceMatrixSavedQty(): ?int
    {
        $cartUdrawData = $this->getCartUdrawData();
        if ($cartUdrawData != null) {
            if (key_exists('udraw_pricematrix_qty', $cartUdrawData)) {
                return intval($cartUdrawData['udraw_pricematrix_qty']);
            }
        }
        return null;
    }

    public function getPriceCurrencySymbol(): string
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    public function getCartUdrawDesign()
    {
        return ($this->isUpdate()) ? $this->udrawHelper->getUdrawDesignFromCartQuoteItem($this->cartItem) : null;
    }

    public function getUdrawApiKey()
    {
        return $this->udrawHelper->getUdrawSettingValue('udraw_api_key');
    }

    public function getUdrawDesignerUI()
    {
        return $this->udrawHelper->getUdrawSettingValue('udraw_designer_ui');
    }

    public function getDesignerCustomJS()
    {
        return base64_decode($this->udrawHelper->getUdrawSettingValue('custom_designer_js'));
    }

    public function getDesignerCustomCSS()
    {
        return base64_decode($this->udrawHelper->getUdrawSettingValue('custom_designer_css'));
    }

    public function getPriceMatrixCustomJS()
    {
        return base64_decode($this->udrawHelper->getUdrawSettingValue('custom_pricematrix_js'));
    }

    public function getPriceMatrixCustomCSS()
    {
        return base64_decode($this->udrawHelper->getUdrawSettingValue('custom_pricematrix_css'));
    }

    public function generatePriceMatrixQtyBreakDropdown(): string
    {
        if ($this->containsPriceMatrix()) {
            $priceMatrixInstance = $this->getPriceMatrixInstance();
            if ($priceMatrixInstance == null) {
                return "";
            }

            $priceBreaks = $this->udrawHelper->getQtyPriceBreaks(base64_decode($priceMatrixInstance->getPriceData()));

            if ($priceBreaks != null) {
                $schema = '<script type="application/ld+json">[';
                $dropDown = '<select name="pm-quantity-breaks" class="pm-quantity-breaks">';
                $priceBreakCount = count($priceBreaks);
                for ($x = 0; $x < $priceBreakCount; $x++) {
                    // Option Element
                    $dropDown .= '<option name="pm-quantity-' . $priceBreaks[$x]["Break"] . '" ';
                    $dropDown .= 'data-qty="' . $priceBreaks[$x]["Break"] . '" ';
                    $dropDown .= 'data-unitprice="' . $priceBreaks[$x]["unitprice"] . '">';

                    // Schema Offer Information
                    $schema .= '{"@context":"http:\/\/schema.org","@type":"Offer",';
                    $schema .= '"priceCurrency":"' . $this->priceCurrency->getCurrency()->toString() . '",';
                    $schema .= '"availability":"InStock",';
                    $schema .= '"url":"' . $this->getProduct()->getProductUrl() . '",';
                    $schema .= '"sku":"' . $this->getProduct()->getSku() . '",';
                    $schema .= '"price":"' . $priceBreaks[$x]["unitprice"] . '",';
                    $schema .= '"eligibleTransactionVolume":{"@type":"PriceSpecification","name":"' . $priceBreaks[$x]["Break"] . '"}';
                    $schema .= ($x == $priceBreakCount - 1) ? '}' : '},';

                    // Price Specification
                    $selectPriceBreak = __(
                        "%1 for %2%3 each - %4%5",
                        $priceBreaks[$x]["Break"],
                        $this->priceCurrency->getCurrencySymbol(),
                        str_replace(".", __("."), $priceBreaks[$x]["unitprice"]),
                        $this->priceCurrency->getCurrencySymbol(),
                        number_format(floatval($priceBreaks[$x]["unitprice"]) * floatval($priceBreaks[$x]["Break"]), 2, __("."), "")
                    );
                    $dropDown .= $selectPriceBreak;
                    $dropDown .= '</option>';
                }
                $dropDown .= '</select>';
                $schema .= ']</script>';
                $dropDown .= $schema;

                return $dropDown;
            }
        }
        return "";
    }

    public function generateAuthToken() : string
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $userSession = uniqid("udraw_");
        if ($isLoggedIn) {
            $userSession = $this->httpContext->getValue('customer_id') . '_' . $this->httpContext->getValue('customer_email');
        }
        return $this->udrawHelper->generateAuthToken(
            $this->udrawHelper->getUdrawSettingValue('udraw_api_key'),
            $this->udrawHelper->getUdrawSettingValue('udraw_secret_key'),
            hash('sha512', $userSession),
            false
        );
    }
}
