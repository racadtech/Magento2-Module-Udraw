<?php
namespace Racadtech\Udraw\Helper;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Racadtech\Udraw\Model\PricematrixFactory;
use Racadtech\Udraw\Model\SettingsFactory;
use Racadtech\Udraw\Model\TemplatesFactory;
use Safe\Exceptions\JsonException;
use stdClass;

class Udraw extends AbstractHelper
{

    /**
     * @var string
     */
    protected string $_apiUrl = 'https://udraw-app.racadtech.com';

    /**
     * @var Json
     */
    protected Json $_serializer;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $_loggerInterface;

    /**
     * @var Curl
     */
    protected Curl $_curlClient;

    /**
     * @var SettingsFactory
     */
    protected SettingsFactory $_udrawSettings;

    /**
     * @var PricematrixFactory
     */
    protected PricematrixFactory $_pricematrixFactory;

    /**
     * @var TemplatesFactory
     */
    protected TemplatesFactory $_templatesFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $_order;

    protected CheckoutSession $_checkoutSession;
    /**
     * @var ProductFactory
     */
    protected ProductFactory $_product;

    public function __construct(
        StoreManagerInterface $storeManager,
        Json $serializer,
        SettingsFactory $udrawSettings,
        PricematrixFactory $pricematrixFactory,
        TemplatesFactory $templatesFactory,
        OrderRepositoryInterface $order,
        ProductFactory $product,
        LoggerInterface $loggerInterface,
        Curl $curl,
        CheckoutSession $checkoutSession,
        Context $context
    ) {
        $this->_loggerInterface = $loggerInterface;
        $this->_curlClient = $curl;
        $this->_serializer = $serializer;
        $this->_storeManager = $storeManager;
        $this->_udrawSettings = $udrawSettings;
        $this->_pricematrixFactory = $pricematrixFactory;
        $this->_templatesFactory = $templatesFactory;
        $this->_order = $order;
        $this->_product = $product;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);

        // Init the Curl Client
        $this->initCurlClient();
    }

    public function getCurlClient()
    {
        return $this->_curlClient;
    }

    /**
     * Returns uDraw's Base API Url.
     *
     * @return string
     */
    public function getApiBaseUrl() : string
    {
        return $this->_apiUrl;
    }

    /**
     * Returns this Magento's Store Base Url.
     *
     * @return string
     */
    public function getStoreBaseUrl(): string
    {
        try {
            return $this->_storeManager->getStore()->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            $this->_loggerInterface->error('uDraw Helper: getStoreBaseUrl() Exception: ' . $e->getMessage());
            return "";
        }
    }

    /**
     * Validates the supplied uDraw ApiKey and uDraw Secret Api Key.
     *
     * @param $apiKey string uDraw Api Key
     * @param $apiSecret string uDraw Api Secret Key
     *
     * @return bool
     */
    public function validateApiCredentials(string $apiKey, string $apiSecret): bool
    {
        $authToken = $this->generateAuthToken($apiKey, $apiSecret, "_internal_", false);
        $request = $this->getApiBaseUrl() . '/designer/validate';
        $request .= '?key=' . $apiKey . '&authToken=' . $authToken . '&host=' . $this->getStoreBaseUrl();

        $this->getCurlClient()->get($request);
        $response = json_decode($this->getCurlClient()->getBody(), true);

        if (is_array($response)) {
            return $response['successful'];
        } else {
            return false; // TODO: Catch this problem and report it somewhere.
        }
    }

    public function requestUdrawDesignPDF(string $apiKey, string $designKey): string
    {
        $request = $this->getApiBaseUrl() . '/Designer/RequestPDF/' . $apiKey . '/' . $designKey;
        $this->getCurlClient()->get($request);
        $response = json_decode($this->getCurlClient()->getBody(), true);
        if (is_array($response)) {
            if ($response['successful']) {
                return $response['message'];
            }
        }
        return "";
    }

    /**
     * Creates a JWT token based on supplied information.
     *
     * @param $apiKey string uDraw Api Key
     * @param $apiSecret string uDraw Api Secret Key
     * @param $userSession string Any unique value ( ie: user session )
     * @param $isAdmin bool If admin, the designer will load in with admin privileges
     * @return mixed
     */
    public function generateAuthToken(string $apiKey, string $apiSecret, string $userSession, bool $isAdmin = false)
    {
        $token = [
            "iss" => $apiKey,
            "sub" => $userSession, // This is any session value to identify your user.
            "axs" => ($isAdmin) ? "admin" : "user", // Access. Can be either "user" or "admin".
            "iat" => time(),
            "exp" => time()+28800 // 8 Hour expiration.
        ];

        return \Firebase\JWT\JWT::encode($token, $apiSecret);
    }

    /**
     * Get uDraw setting value. If value is not found, function will return null.
     *
     * @param string $settingName
     * @return mixed
     */
    public function getUdrawSettingValue(string $settingName)
    {
        $settings = $this->_udrawSettings->create()->getCollection();
        foreach ($settings as $udrawSetting) {
            if (strtolower($udrawSetting->getName()) == strtolower($settingName)) {
                return $udrawSetting->getValue();
            }
        }
        return null;
    }

    /**
     * Get a list of all uDraw setting sections
     *
     * @return array
     */
    public function getUdrawSettingSections(): array
    {
        $sections = [];
        $settings = $this->_udrawSettings->create()->getCollection();
        foreach ($settings as $udrawSetting) {
            if (!in_array($udrawSetting->getSection(), $sections)) {
                array_push($sections, $udrawSetting->getSection());
            }
        }

        return $sections;
    }

    /**
     * Updates an existing uDraw setting with new value.
     *
     * @param string $settingName
     * @param $settingValue
     */
    public function setUdrawSetting(string $settingName, $settingValue)
    {
        $settings = $this->_udrawSettings->create()->getCollection();
        $foundMatch = false;
        foreach ($settings as $udrawSetting) {
            if (strtolower($udrawSetting->getName()) == strtolower($settingName)) {
                $udrawSetting->setValue($settingValue);
                $foundMatch = true;
                break;
            }
        }
        // Store new settings into database if we found a match.
        if ($foundMatch) {
            $settings->save();
        }
    }

    /**
     * Gets Pricematrix instance by Access Key.
     *
     * @param string $accessKey
     * @return mixed|null
     */
    public function getPricematrixInstance(string $accessKey)
    {
        $priceMatrixCollection = $this->_pricematrixFactory->create()->getCollection();
        foreach ($priceMatrixCollection as $priceMatrix) {
            if ($priceMatrix->getAccessKey() == $accessKey) {
                return $priceMatrix;
            }
        }
        return null;
    }

    /**
     * Gets Template instance by Access Key.
     *
     * @param string $accessKey
     * @return mixed|null
     */
    public function getTemplateInstance(string $accessKey)
    {
        $templatesCollection = $this->_templatesFactory->create()->getCollection();
        foreach ($templatesCollection as $template) {
            if ($template->getAccessKey() == $accessKey) {
                return $template;
            }
        }
        return null;
    }

    /**
     * Get Pricematrix Db collection.
     *
     * @return AbstractDb|AbstractCollection|null
     */
    public function getPricematrixCollection()
    {
        return $this->_pricematrixFactory->create()->getCollection();
    }

    /**
     * Get uDraw Db collection.
     *
     * @return AbstractDb|AbstractCollection|null
     */
    public function getTemplatesCollection()
    {
        return $this->_templatesFactory->create()->getCollection();
    }

    /**
     * Gets Udraw Specific data from the order.
     *
     * @param int $orderId Order Id
     * @return array
     */
    public function getUdrawDataFromOrder(int $orderId): array
    {
        $order = $this->_order->get($orderId);
        if ($order == null) {
            return [];
        }

        $udrawData = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $options = $orderItem->getProductOptions();
            if (key_exists('udraw_data', $options)) {
                $udrawDataItem = [];
                $udrawDataItem['order_id'] = $orderId;
                $udrawDataItem['order_item_id'] = intval($orderItem->getItemId());
                $udrawDataItem['product_id'] = intval($orderItem->getProductId());
                $udrawDataItem['product_name'] = $this->_product->create()->load($orderItem->getProductId())->getName();

                $udrawDataItem['product_preview'] = '';
                if (key_exists('udraw_pricematrix_product_preview', $options['udraw_data'])) {
                    if (strlen($options['udraw_data']['udraw_pricematrix_product_preview']) > 1) {
                        // Found a price matrix preview inside order item.
                        $udrawDataItem['product_preview'] = $options['udraw_data']['udraw_pricematrix_product_preview'];
                    }
                }

                $udrawDataItem['udraw_designer_preview'] = null;
                if (key_exists('udraw_designer_data', $options['udraw_data'])) {
                    $udrawDesignerData = $this->convertEncodedUdrawDesignToObject($options['udraw_data']['udraw_designer_data']);
                    if (!is_null($udrawDesignerData)) {
                        if (property_exists($udrawDesignerData, 'preview')) {
                            // Found designer preview, will override product preview with this value.
                            $udrawDataItem['product_preview'] = $this->getApiBaseUrl() . $udrawDesignerData->preview;
                            $udrawDataItem['udraw_designer_preview'] = $udrawDataItem['product_preview'];
                        }
                    }
                    $options['udraw_data']['udraw_designer_data'] = $udrawDesignerData;
                }
                $udrawDataItem['udraw_gosendex_uploaded_artwork'] = null;
                if (key_exists('udraw_gosendex_uploaded_artwork', $options['udraw_data'])) {
                    $udrawDataItem['udraw_gosendex_uploaded_artwork'] = json_decode(base64_decode($options['udraw_data']['udraw_gosendex_uploaded_artwork']));
                }
                $udrawDataItem['udraw_data'] = $options['udraw_data'];

                array_push($udrawData, $udrawDataItem);
            }
        }

        return $udrawData;
    }

    /**
     * Returns Udraw data from a cart id. If Udraw data doesn't exist, will return null.
     *
     * @param int $cartId
     * @return array|null
     */
    public function getUdrawDataFromCartId(int $cartId) : ?array
    {
        $cartItem = $this->getCartItemById($cartId);
        if ($cartItem != null) {
            return $this->getUdrawDataFromCartQuoteItem($cartItem);
        }
        return null;
    }

    public function calculateQtyPriceFromQuoteItem(QuoteItem $quoteItem, int $qty): ?float
    {
        $udrawData = $this->getUdrawDataFromCartQuoteItem($quoteItem);
        if ($udrawData != null) {
            if (key_exists('udraw_pricematrix_price_breaks', $udrawData) &&
                key_exists('pricematrix_selected_options', $udrawData)) {
                $priceBreaks = $this->convertEncodedDataToObject($udrawData['udraw_pricematrix_price_breaks']);
                $selectedOptions = $this->convertEncodedDataToObject($udrawData['pricematrix_selected_options']);

                if (is_array($priceBreaks) && is_array($selectedOptions)) {
                    $qtyPriceParts = [];

                    for ($x = 0; $x < count($selectedOptions); $x++) {
                        for ($y = 0; $y < count($priceBreaks); $y++) {
                            $priceBreakName = $priceBreaks[$y]->Name;
                            $selectedPriceName = $selectedOptions[$x]->price_name;

                            if ($priceBreakName == $selectedPriceName) {
                                if ($qty >= $priceBreaks[$y]->Break) {
                                    $qtyPriceParts[$priceBreakName] = floatval($priceBreaks[$y]->UnitPrice);
                                }
                            }
                        }
                    }

                    for ($x = 0; $x < count($priceBreaks); $x++) {
                        if ($priceBreaks[$x]->Name == "__quantity") {
                            if ($qty >= $priceBreaks[$x]->Break) {
                                $qtyPriceParts[$priceBreaks[$x]->Name] = floatval($priceBreaks[$x]->UnitPrice);
                            }
                        }
                    }

                    $totalBaseQtyPrice = floatval(0);
                    foreach ($qtyPriceParts as $key => $value) {
                        $totalBaseQtyPrice += floatval($value);
                    }

                    return ($totalBaseQtyPrice > 0) ? $totalBaseQtyPrice : null;
                }
            }
        }

        return null;
    }

    public function getQtyPriceBreaks(string $priceMatrixXML): ?array
    {
        $xmlDoc = simplexml_load_string($priceMatrixXML);
        if ($xmlDoc !== false) {
            $defaultOptions = [];
            $defaultOptionAdditionalPrice = 0.00;
            $qtyPriceBreaks = [];

            if (isset($xmlDoc->Options->Option)) {
                for ($x = 0; $x < count($xmlDoc->Options->Option); $x++) {
                    if (isset($xmlDoc->Options->Option[$x]->attributes()->Name) &&
                        isset($xmlDoc->Options->Option[$x]->attributes()->Prices)) {
                        if (!key_exists((string)$xmlDoc->Options->Option[$x]->attributes()->Name, $defaultOptions)) {
                            $defaultOptions[(string)$xmlDoc->Options->Option[$x]->attributes()->Name] = (string)$xmlDoc->Options->Option[$x]->attributes()->Prices;
                        }
                    }
                }
            }

            if (isset($xmlDoc->Prices->Price)) {
                for ($x = 0; $x < count($xmlDoc->Prices->Price); $x++) {
                    if (isset($xmlDoc->Prices->Price[$x]->attributes()->Name) &&
                        isset($xmlDoc->Prices->Price[$x]->attributes()->Break) &&
                        isset($xmlDoc->Prices->Price[$x]->attributes()->unitprice)) {
                        foreach ($defaultOptions as $key => $value) {
                            if ($value == (string)$xmlDoc->Prices->Price[$x]->attributes()->Name) {
                                $defaultOptionAdditionalPrice +=
                                    floatval((string)$xmlDoc->Prices->Price[$x]->attributes()->unitprice);
                                break;
                            }
                        }
                    }
                }

                for ($x = 0; $x < count($xmlDoc->Prices->Price); $x++) {
                    if (isset($xmlDoc->Prices->Price[$x]->attributes()->Name) &&
                        isset($xmlDoc->Prices->Price[$x]->attributes()->Break) &&
                        isset($xmlDoc->Prices->Price[$x]->attributes()->unitprice)) {
                        if ((string)$xmlDoc->Prices->Price[$x]->attributes()->Name == "__quantity") {
                            if (floatval((string)$xmlDoc->Prices->Price[$x]->attributes()->unitprice) > 0) {
                                $totalAdjustedPrice = $this->formatNumber(
                                    strval(floatval((string)$xmlDoc->Prices->Price[$x]->attributes()->unitprice) + $defaultOptionAdditionalPrice)
                                );

                                $qtyPriceBreak = [
                                    "Name" => (string)$xmlDoc->Prices->Price[$x]->attributes()->Name,
                                    "Break" => (string)$xmlDoc->Prices->Price[$x]->attributes()->Break,
                                    "unitprice" => $totalAdjustedPrice
                                ];

                                $qtyPriceBreaks[] = $qtyPriceBreak;
                            }
                        }
                    }
                }

                return $qtyPriceBreaks;
            }
        }

        return null;
    }

    /**
     * Attempts to get either the preview image from uDraw design or preview from price matrix selection.
     * @param QuoteItem $quoteItem
     * @return string|null
     */
    public function getUdrawPreviewFromCartQuoteItem(QuoteItem $quoteItem): ?string
    {
        $cartUdrawData = $this->getUdrawDataFromCartQuoteItem($quoteItem);
        if ($cartUdrawData != null) {
            if (key_exists('udraw_designer_data', $cartUdrawData)) {
                $udrawDesignerData = $this->convertEncodedUdrawDesignToObject($cartUdrawData['udraw_designer_data']);
                if (!is_null($udrawDesignerData)) {
                    return $this->getApiBaseUrl() . $udrawDesignerData->preview;
                }
            }
            if (key_exists('udraw_pricematrix_product_preview', $cartUdrawData)) {
                if (strlen($cartUdrawData['udraw_pricematrix_product_preview']) > 1) {
                    return $cartUdrawData['udraw_pricematrix_product_preview'];
                }
            }
        }
        return null;
    }

    /**
     * Return's Udraw design specific information if it exists for the cart quote item.
     * @param QuoteItem $quoteItem
     * @return StdClass|null
     */
    public function getUdrawDesignFromCartQuoteItem(QuoteItem $quoteItem)
    {
        $cartUdrawData = $this->getUdrawDataFromCartQuoteItem($quoteItem);
        if ($cartUdrawData != null) {
            if (key_exists('udraw_designer_data', $cartUdrawData)) {
                return $this->convertEncodedUdrawDesignToObject($cartUdrawData['udraw_designer_data']);
            }
        }
        return null;
    }

    /**
     * Returns Udraw specific data from Quote Item if it exists.
     *
     * @param QuoteItem $quoteItem
     * @return array|null
     */
    public function getUdrawDataFromCartQuoteItem(QuoteItem $quoteItem) : ?array
    {
        $customAttribute = $quoteItem->getOptionByCode('udraw_data');
        if ($customAttribute) {
            return $this->_serializer->unserialize($customAttribute->getValue());
        }
        return null;
    }

    /**
     * Return's the quote item based on the cart id.
     *
     * @param int $cartId
     * @return QuoteItem|null
     */
    public function getCartItemById(int $cartId): ?QuoteItem
    {
        try {
            $cartItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            for ($x = 0; $x < count($cartItems); $x++) {
                if ($cartItems[$x]->getId() == $cartId) {
                    return $cartItems[$x];
                }
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            // We will just ignore and return null if there was a problem looking through cart items.
        }
        return null;
    }

    /**
     * Converted the encoded Udraw Design string to object.
     *
     * @param $encodedUdrawDesign
     * @return stdClass|null
     */
    public function convertEncodedUdrawDesignToObject($encodedUdrawDesign) : ?stdClass
    {
        $udrawDesignerData = $this->_serializer->serialize(base64_decode($encodedUdrawDesign));
        try {
            return \Safe\json_decode($this->_serializer->unserialize($udrawDesignerData));
        } catch (JsonException $e) {
            return null;
        }
    }

    public function convertEncodedDataToObject($encodedData)
    {
        try {
            return \Safe\json_decode(base64_decode($encodedData));
        } catch (JsonException $e) {
            return null;
        }
    }

    public function countDecimalPlaces(string $number): int
    {
        $locale_info = localeconv();
        $pos = strrpos($number, $locale_info['decimal_point']);
        if ($pos !== false) {
            return strlen($number) - ($pos + 1);
        }
        return 0;
    }

    public function formatNumber(string $number) : string
    {
        $decimalPlaces = $this->countDecimalPlaces($number);
        return ($decimalPlaces < 2) ?
            number_format($number, "2", ".", "") :
            $number;
    }

    private function initCurlClient()
    {
        $this->getCurlClient()->addHeader("Content-Type", "application/json");
        $this->getCurlClient()->setOption(CURLOPT_RETURNTRANSFER, true);
    }
}
