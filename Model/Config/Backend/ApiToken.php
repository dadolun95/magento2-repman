<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Model\Config\Backend;

use Dadolun\Repman\Model\RestApiClient;
use Dadolun\Repman\Helper\Configuration;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ApiToken
 * @package Dadolun\Repman\Model\Config\Backend
 */
class ApiToken extends Value
{
    /**
     * @var RestApiClient
     */
    protected $restApiClient;

    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * ApiToken constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Configuration $configHelper
     * @param ManagerInterface $messageManager
     * @param RestApiClient $restApiClient
     * @param Json $json
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Configuration $configHelper,
        ManagerInterface $messageManager,
        RestApiClient $restApiClient,
        Json $json,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->restApiClient = $restApiClient;
        $this->configHelper = $configHelper;
        $this->messageManager = $messageManager;
        $this->json = $json;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value|void
     */
    public function beforeSave()
    {
        $this->_dataSaveAllowed = false;
        $value = (string)$this->getValue();
        try {
            /**
             * @var RestApiClient $restApiClient
             */
            $this->restApiClient->authorizeClient($value);
            $response = $this->json->unserialize($this->restApiClient->getOrganization());
            if (isset($response["total"])) {
                $this->configHelper->setValue('api_token_status', 1);
                $this->_dataSaveAllowed = true;
            }
        } catch (\Exception $e) {
            $this->_dataSaveAllowed = false;
            $this->messageManager->addErrorMessage(__('Invalid API token setted up'));
        }
        $this->setValue($value);
    }
}
