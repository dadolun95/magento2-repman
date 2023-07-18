<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Controller\Purchased;

use Composer\Package\CompletePackage;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Composer\ComposerFactory;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositorySet;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Composer\Factory;
use Dadolun\Repman\Helper\Configuration;
use Composer\IO\BufferIO;
use Magento\Customer\Model\Session;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory as PurchasedLinkCollectionFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Download
 * @package Dadolun\Repman\Controller\Purchased
 */
class Download extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    const INSTALL_FILE = "INSTALL.txt";

    /**
     * @var ForwardFactory
     */
    protected $rawFactory;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ComposerFactory
     */
    protected $composerFactory;

    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PurchasedLinkCollectionFactory
     */
    protected $purchasedLinkCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Download constructor.
     * @param Context $context
     * @param RawFactory $rawFactory
     * @param RedirectFactory $redirectFactory
     * @param ComposerFactory $composerFactory
     * @param Configuration $configHelper
     * @param Session $customerSession
     * @param PurchasedLinkCollectionFactory $purchasedLinkCollectionFactory
     * @param DateTime $dateTime
     * @param Filesystem $fileSystem
     * @param FileFactory $fileFactory
     * @param ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        RawFactory $rawFactory,
        RedirectFactory $redirectFactory,
        ComposerFactory $composerFactory,
        Configuration $configHelper,
        Session $customerSession,
        PurchasedLinkCollectionFactory $purchasedLinkCollectionFactory,
        DateTime $dateTime,
        Filesystem $fileSystem,
        FileFactory $fileFactory,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->rawFactory = $rawFactory;
        $this->redirectFactory = $redirectFactory;
        $this->composerFactory = $composerFactory;
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
        $this->purchasedLinkCollectionFactory = $purchasedLinkCollectionFactory;
        $this->dateTime = $dateTime;
        $this->fileSystem = $fileSystem;
        $this->fileFactory = $fileFactory;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @return ResponseInterface|Raw|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRaw = $this->rawFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            try {
                $packageName = $this->getRequest()->getParam("package");
                $purchaseId = $this->getRequest()->getParam("purchase");
                $customerId = $this->customerSession->getCustomerId();

                /**
                 * @var Purchased $purchasedLink
                 */
                $purchasedLink = $this->purchasedLinkCollectionFactory->create()
                    ->addFieldToFilter("customer_id", $customerId)
                    ->addFieldToFilter("purchased_id", $purchaseId)
                    ->getFirstItem();

                if ($purchasedLink->getData("repman_uid")) {
                    $subscriptionEndDate = $purchasedLink->getData("repman_subscription_end");
                    $config = $this->getRepmanComposerConfig();
                    $composer = Factory::create(
                        new BufferIO(),
                        $config
                    );
                    $localRepo = $composer->getRepositoryManager()->getLocalRepository();
                    $repo = new CompositeRepository(array_merge([$localRepo], $composer->getRepositoryManager()->getRepositories()));
                    $minStability = $composer->getPackage()->getMinimumStability();

                    $repoSet = new RepositorySet($minStability);
                    $repoSet->addRepository($repo);
                    $packages = $repoSet->findPackages(strtolower($packageName));
                    $tmpFolder = $customerId . "_" . $this->dateTime->gmtTimestamp();
                    $varDirectoryRead = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
                    $tmpFolderPath = $varDirectoryRead->getAbsolutePath() . $tmpFolder;

                    $zipArchivePath = $tmpFolder . ".zip";
                    $zip = new \ZipArchive();
                    $zip->open($zipArchivePath, \ZipArchive::CREATE);
                    foreach ($packages as $package) {
                        if ($this->checkPackageDate($subscriptionEndDate, $package)) {
                            $composer->getArchiveManager()->archive($package, "zip", $tmpFolderPath, $package->getVersion());
                        }
                        $zip->addFile($tmpFolderPath . "/" . $package->getVersion() . ".zip", $package->getVersion() . ".zip");
                    }
                    $this->generateInstallationFile($tmpFolder, $purchasedLink, $subscriptionEndDate);
                    $zip->addFile($tmpFolderPath . "/" . self::INSTALL_FILE, self::INSTALL_FILE);
                    $zip->close();

                    $archiveContent = file_get_contents($zipArchivePath);
                    $varDirectoryWrite = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                    $this->fileFactory->create(
                        str_replace("\/", "_", $packageName) . ".zip",
                        $archiveContent,
                        DirectoryList::VAR_DIR
                    );
                    $varDirectoryWrite->delete("/" . $tmpFolder);
                    $varDirectoryWrite->delete("/" . $tmpFolder . ".zip");
                } else {
                    $this->messageManager->addErrorMessage(__("Not Authorized to download."));
                    return $this->redirectFactory->create()->setPath('downloadable/customer/products');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__("An error occurred downloading your product. Please try again"));
                return $this->redirectFactory->create()->setPath('downloadable/customer/products');
            }
        } else {
            $this->messageManager->addErrorMessage(__("Not Authorized to download."));
            return $this->redirectFactory->create()->setPath('downloadable/customer/products');
        }
        return $resultRaw;
    }

    /**
     * @return array
     */
    private function getRepmanComposerConfig() {
        $repmanOrganizationUrl = $this->configHelper->getValue("main_organization");
        return [
            "config" => [
                "http-basic" => [
                    "$repmanOrganizationUrl.repo.repman.io" => [
                        "username" => "token",
                        "password" => $this->configHelper->getMainOrganizationToken($repmanOrganizationUrl)
                    ]
                ],
            ],
            "repositories" => [
                "repman" => [
                    "type" => "composer",
                    "url" => "https://$repmanOrganizationUrl.repo.repman.io"
                ]
            ]
        ];
    }

    /**
     * @param string $subscriptionEndDate
     * @param CompletePackage $package
     * @return bool
     */
    private function checkPackageDate($subscriptionEndDate, $package) {
        return $this->dateTime->timestamp($subscriptionEndDate) >= $package->getReleaseDate()->getTimestamp();
    }

    /**
     * @param string $tmpFolder
     * @param Purchased $purchased
     * @param string $subscriptionEndDate
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    private function generateInstallationFile($tmpFolder, $purchased, $subscriptionEndDate) {
        if ($this->dateTime->timestamp($subscriptionEndDate) >= $this->dateTime->timestamp()) {
            $product = $this->productRepository->get($purchased->getProductSku());
            $contents = $product->getData("repman_installation_instructions");
            $purchased->setData("repman_package_installation", $contents);
            $purchased->save();
        }
        $contents = $purchased->getData("repman_package_installation");
        $varFolderWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($contents) {
            $varFolderWriter->writeFile("$tmpFolder/" . self::INSTALL_FILE, $contents);
        }
    }
}
