<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Observer\Adminhtml;

use Dadolun\Repman\Helper\Configuration;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

/**
 * Class OrderCompleted
 * @package Dadolun\Repman\Observer
 */
class SaveCustomerData implements ObserverInterface
{
    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerResourceFactory
     */
    protected $customerResourceFactory;

    /**
     * SaveCustomerData constructor.
     * @param Configuration $configHelper
     * @param CustomerFactory $customerFactory
     * @param CustomerResourceFactory $customerResourceFactory
     */
    public function __construct(
        Configuration $configHelper,
        CustomerFactory $customerFactory,
        CustomerResourceFactory $customerResourceFactory
    )
    {
        $this->configHelper = $configHelper;
        $this->customerFactory = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if ($this->configHelper->getFlag('enable') && $this->configHelper->getFlag('api_token_status')) {
            /**
             * @var RequestInterface $request
             */
            $request = $observer->getEvent()->getRequest();
            /**
             * @var CustomerData $customerData
             */
            $customerData = $observer->getEvent()->getCustomer();
            /**
             * @var Customer $customer
             */
            $customer = $this->customerFactory->create();
            $customerData->setCustomAttribute('repman_id', $request->getParam("customer")["repman_id"]);
            $customerData->setCustomAttribute('repman_token', $request->getParam("customer")["repman_token"]);
            $customer->updateData($customerData);
            /**
             * @var CustomerResource $customerResource
             */
            $customerResource = $this->customerResourceFactory->create();
            $customerResource->saveAttribute($customer, 'repman_id');
            $customerResource->saveAttribute($customer, 'repman_token');
        }
    }
}
