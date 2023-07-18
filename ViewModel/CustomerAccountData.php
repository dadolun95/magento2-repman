<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\ViewModel;

use Dadolun\Repman\Helper\Configuration;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class PurchasedDownloadRepmanData
 * @package Dadolun\Repman\ViewModel
 */
class CustomerAccountData implements ArgumentInterface
{

    /**
     * @var Configuration
     */
    private $configHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * CustomerAccountData constructor.
     * @param Configuration $configHelper
     * @param Session $customerSession
     */
    public function __construct(
        Configuration $configHelper,
        Session $customerSession
    )
    {
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return AttributeInterface|null
     */
    public function getOrganizationToken()
    {
        if ($this->configHelper->getFlag("enable")) {
            if ($repmanTokenAttribute = $this->customerSession->getCustomer()->getDataModel()->getCustomAttribute("repman_token")) {
                return $repmanTokenAttribute->getValue();
            }
        }
        return null;
    }
}
