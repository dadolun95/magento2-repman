<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Dadolun\Repman\Model\RestApiClient;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Configuration
 * @package Dadolun\SibOrderSync\Helper
 */
class Configuration extends AbstractHelper
{
    const CONFIG_SECTION_PATH = 'dadolun_repman';
    const CONFIG_GROUP_ORDER_PATH = 'repman';
    const MODULE_CONFIG_PATH = self::CONFIG_SECTION_PATH . '/' . self::CONFIG_GROUP_ORDER_PATH;
    const DEFAULT_TOKEN_NAME = "M2";

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var RestApiClient
     */
    protected $restApiClient;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Configuration constructor.
     * @param Context $context
     * @param WriterInterface $configWriter
     * @param RestApiClient $restApiClient
     * @param Json $json
     */
    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        RestApiClient $restApiClient,
        Json $json
    ) {
        $this->configWriter = $configWriter;
        $this->restApiClient = $restApiClient;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @param $val
     * @return mixed
     */
    public function getValue($val) {
        return $this->scopeConfig->getValue(self::MODULE_CONFIG_PATH . '/' . $val, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $pathVal
     * @param $val
     * @param string $scope
     * @param int $scopeId
     */
    public function setValue($pathVal, $val, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0) {
        $this->configWriter->save(self::MODULE_CONFIG_PATH . '/' . $pathVal, $val, $scope, $scopeId);
    }

    /**
     * @param $val
     * @return bool
     */
    public function getFlag($val) {
        return $this->scopeConfig->isSetFlag(self::MODULE_CONFIG_PATH . '/' . $val, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $organization
     * @return mixed
     */
    public function getMainOrganizationToken($organization) {
        $this->restApiClient->authorizeClient($this->getValue('api_token'));
        $tokens =  $this->json->unserialize($this->restApiClient->getOrganizationTokens($organization));
        return array_first($tokens["data"])["value"];
    }
}
