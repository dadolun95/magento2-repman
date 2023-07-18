<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\ViewModel;

use Magento\Downloadable\Model\Link\Purchased;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\UrlInterface;

/**
 * Class PurchasedDownloadRepmanData
 * @package Dadolun\Repman\ViewModel
 */
class PurchasedDownloadRepmanData implements ArgumentInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * PurchasedDownloadRepmanData constructor.
     * @param DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     * @param UrlInterface $url
     */
    public function __construct(
        DateTime $dateTime,
        ProductRepositoryInterface $productRepository,
        UrlInterface $url
    )
    {
        $this->dateTime = $dateTime;
        $this->productRepository = $productRepository;
        $this->url = $url;
    }

    /**
     * @param Purchased $purchased
     * @return \Magento\Framework\Phrase|string
     */
    public function getSubscriptionEndOfLife($purchased)
    {
        if ($purchased->getData("repman_uid")) {
            if ($purchased->getData("repman_subscription_end")) {
                return $this->dateTime->date("Y-m-d", $purchased->getData("repman_subscription_end"));
            } else {
                return __("Unlimited");
            }
        }
        return __("Order not completed yet");
    }

    /**
     * @param Item $purchasedItem
     * @return string|null
     */
    public function getRepmanPackageName($purchasedItem)
    {
        try {
            $product = $this->productRepository->getById($purchasedItem->getProductId());
            return urlencode($product->getData("repman_repository"));
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * @param string $packageName
     * @param Purchased $purchased
     * @return string
     */
    public function getPackageDownloadUrl($packageName, $purchased) {
        $purchaseId = $purchased->getId();
        return $this->url->getUrl("repman/purchased/download/package/$packageName/purchase/$purchaseId");
    }
}
