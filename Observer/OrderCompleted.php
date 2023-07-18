<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Observer;

use Dadolun\Repman\Helper\Configuration;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Dadolun\Repman\Model\RestApiClient;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory as PurchasedCollectionFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceFactory;
use Magento\Customer\Model\CustomerFactory;
use Dadolun\Repman\Helper\Logger;

/**
 * Class OrderCompleted
 * @package Dadolun\Repman\Observer
 */
class OrderCompleted implements ObserverInterface
{
    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var RestApiClient
     */
    protected $restApiClient;

    /**
     * @var PurchasedCollectionFactory
     */
    protected $purchasedCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerResourceFactory
     */
    protected $customerResourceFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * OrderCompleted constructor.
     * @param Configuration $configHelper
     * @param RestApiClient $restApiClient
     * @param PurchasedCollectionFactory $purchasedCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Json $json
     * @param DateTime $dateTime
     * @param CustomerResourceFactory $customerResourceFactory
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Logger $logger
     */
    public function __construct(
        Configuration $configHelper,
        RestApiClient $restApiClient,
        PurchasedCollectionFactory $purchasedCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Json $json,
        DateTime $dateTime,
        CustomerResourceFactory $customerResourceFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Logger $logger
    )
    {
        $this->configHelper = $configHelper;
        $this->restApiClient = $restApiClient;
        $this->purchasedCollectionFactory = $purchasedCollectionFactory;
        $this->productRepository = $productRepository;
        $this->json = $json;
        $this->dateTime = $dateTime;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if ($this->configHelper->getFlag('enable') && $this->configHelper->getFlag('api_token_status')) {
            try {
                $this->restApiClient->authorizeClient($this->configHelper->getValue('api_token'));
                /** @var Invoice $invoice */
                $invoice = $observer->getEvent()->getInvoice();
                /** @var Order $order */
                $order = $invoice->getOrder();
                $orderId = $order->getId();
                if ($this->needRepmanSync($order)) {
                    $customerId = $order->getCustomerId();
                    if ($customerId) {
                        /** @var Customer $customerData */
                        $customerData = $this->customerRepository->getById($customerId);
                        $organizationPrefix = $this->configHelper->getValue('organization_prefix');
                        $organization = $organizationPrefix . "-" . $customerData->getId();
                        /**
                         * @var CustomerResource $customerResource
                         */
                        $customerResource = $this->customerResourceFactory->create();
                        $customer = $this->customerFactory->create();
                        if ($customerData->getCustomAttribute('repman_id') === null) {
                            $response = $this->restApiClient->createOrganization($organization);
                            $responseData = $this->json->unserialize($response);
                            $customerData->setCustomAttribute('repman_id', $responseData["id"]);
                            $customer->updateData($customerData);
                            $customerResource->saveAttribute($customer, 'repman_id');
                            $organization = $responseData["alias"];
                        } else {
                            $response = $this->restApiClient->getOrganization($organization);
                            $responseData = $this->json->unserialize($response);
                            foreach ($responseData["data"] as $organizationData) {
                                if ($organizationData["id"] === $customerData->getCustomAttribute('repman_id')->getValue()) {
                                    $organization = $organizationData["alias"];
                                }
                            }
                        }
                        if ($customerData->getCustomAttribute('repman_token') === null) {
                            $response = $this->restApiClient->createOrganizationToken($organization, Configuration::DEFAULT_TOKEN_NAME);
                            $responseData = $this->json->unserialize($response);
                            $customerData->setCustomAttribute('repman_token', $responseData["value"]);
                            $customer->updateData($customerData);
                            $customerResource->saveAttribute($customer, 'repman_token');
                        }
                        $this->syncCustomerPackages($orderId, $customerData, $organization);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error(__("Error making subscription for #%1 order: %2", $orderId, $e->getMessage()));
            }
        }
        return $this;
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function needRepmanSync($order) {
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getProductType() === Type::TYPE_DOWNLOADABLE && $orderItem->getProduct()->getData("repman_repository")) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $orderId
     * @param Customer $customer
     * @param string $organization
     * @throws NoSuchEntityException
     */
    private function syncCustomerPackages($orderId, $customer, $organization) {
        /**
         * @var Purchased[] $purchasedPackages
         */
        $purchasedPackages = $this->purchasedCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('order_id', $orderId)
            ->getItems();
        foreach ($purchasedPackages as $purchasedPackage) {
            if ($purchasedPackage->getData("repman_uid") === null) {
                $product = $this->productRepository->get($purchasedPackage->getProductSku());
                if ($product->getData("repman_repository") !== null) {
                    if ($repmanPackageData = $this->needPackageCreation($organization, $product->getData("repman_repository")) === true) {
                        $response = $this->restApiClient->createOrganizationPackage(
                            $organization,
                            $product->getData("repman_repository"),
                            $product->getData("repman_repository_type"),
                            0
                        );
                        $response = $this->json->unserialize($response);
                        if (isset($response["id"])) {
                            $purchasedPackage->setData("repman_uid", $response["id"]);
                            if ($product->getData("repman_eof_expression")) {
                                $afterOneYearDate = $product->getData("repman_eof_expression");
                                $timeStamp = $this->dateTime->timestamp($afterOneYearDate);
                                $expireDate = $this->dateTime->gmtDate('Y-m-d', $timeStamp);
                                $purchasedPackage->setData("repman_subscription_end", $expireDate);
                            }
                            $purchasedPackage->setData("repman_package_installation", $product->getData("repman_installation_instructions"));
                            $purchasedPackage->save();
                        }
                    } else {
                        $purchasedPackage->setData("repman_uid", $repmanPackageData["id"]);
                        if ($product->getData("repman_eof_expression")) {
                            $afterOneYearDate = $product->getData("repman_eof_expression");
                            $timeStamp = $this->dateTime->timestamp($afterOneYearDate);
                            $expireDate = $this->dateTime->gmtDate('Y-m-d', $timeStamp);
                            $purchasedPackage->setData("repman_subscription_end", $expireDate);
                        }
                        $purchasedPackage->setData("repman_package_installation", $product->getData("repman_installation_instructions"));
                        $purchasedPackage->save();
                    }
                }
            }
        }
    }

    /**
     * @param string $organization
     * @param string $repositoryUrl
     * @return bool|mixed
     */
    private function needPackageCreation($organization, $repositoryUrl) {
        $organizationPackages = $this->json->unserialize($this->restApiClient->getOrganizationPackages($organization));
        if (isset($organizationPackages["data"])) {
            foreach ($organizationPackages["data"] as $package) {
                if ($package["url"] === $repositoryUrl) {
                    return $package;
                }
            }
        }
        return true;
    }
}
