<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use \Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package Dadolun\Repman\Helper
 */
class Logger extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logger constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param $message
     */
    public function info($message) {
        $this->logger->info($message);
    }

    /**
     * @param $message
     */
    public function error($message) {
        $this->logger->error($message);
    }
}
