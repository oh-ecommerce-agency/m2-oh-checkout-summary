<?php
declare(strict_types=1);

namespace OH\CheckoutSuccessSummary\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Success implements ArgumentInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var StoreManagerInterface
     */
    private $storeConfig;

    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    private $imageBuilder;

    public function __construct(
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        StoreManagerInterface $storeConfig,
        UrlInterface $url,
        Session $checkoutSession
    ) {
        $this->url = $url;
        $this->imageBuilder = $imageBuilder;
        $this->currencyCode = $currencyFactory->create();
        $this->checkoutSession = $checkoutSession;
        $this->storeConfig = $storeConfig;
    }

    public function getLastRealOrderId()
    {
        return $this->checkoutSession->getLastOrderId();
    }

    public function getLastRealOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function getProductSummary()
    {
        $products = [];
        $order = $this->checkoutSession->getLastRealOrder();

        foreach ($order->getAllVisibleItems() as $item) {
            $_prod = new DataObject();
            $_prod->setData([
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'qty' => number_format((int)$item->getQtyOrdered(), 0, '.', ''),
                'price' => $item->getPrice(),
                'product_obj' => $item->getProduct()
            ]);
            $products[] = $_prod;
        }

        return $products;
    }

    public function getCurrencySymbol()
    {
        $currentCurrency = $this->storeConfig->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }

    public function getImage($product)
    {
        $image = $this->imageBuilder->create($product, 'cart_page_product_thumbnail', []);
        return $image ? $image->getImageUrl() : '';
    }
}