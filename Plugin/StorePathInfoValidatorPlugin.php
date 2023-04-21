<?php

namespace Blackbird\MultiLangStore\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class StorePathInfoValidatorPlugin
{
    public const SPECIFIC_STORE_CODE_IN_URL_XML_PATH = 'web/url/specific_store_code_in_url';
    protected $scopeConfig;
    protected $storeManager;
    protected $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * @param StorePathInfoValidator $subject
     * @param ?string                $result
     * @param Http                   $request
     * @param string                 $pathInfo
     *
     * @return ?string
     */
    public function afterGetValidStoreCode(
        StorePathInfoValidator $subject, ?string $result, Http $request, string $pathInfo = ''
    ): ?string {

        if (is_null($result) && method_exists(
                $this->request,
                'getServer')) {
            $mageRunCode = $this->request->getServer(StoreManager::PARAM_RUN_CODE);
            $mageRunType = $this->request->getServer(StoreManager::PARAM_RUN_TYPE);

            if (is_string($mageRunCode) && $mageRunCode !== '') {
                try {
                    $store = $this->getStoreByRunCodeAndRunType($mageRunCode, $mageRunType);
                } catch (NoSuchEntityException $noSuchEntityException) {
                    return null;
                }

                $specificStoreCodeInUrl = (string) $this->scopeConfig->getValue(
                    self::SPECIFIC_STORE_CODE_IN_URL_XML_PATH,
                    ScopeInterface::SCOPE_STORE,
                    $store
                );

                return $specificStoreCodeInUrl ?: $result;
            }
        }

        return $result;
    }

    /**
     * Get store object by storeCode
     *
     * @param string $runCode
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getStoreByRunCodeAndRunType(string $runCode, string $runType): StoreInterface
    {
        if($runType === 'website')
        {
            $website = $this->storeManager->getWebsite($runCode);
            $defaultGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
            return $this->storeManager->getStore($defaultGroup->getDefaultStoreId());
        }

        /** @var StoreInterface */
        return $this->storeManager->getStore($runCode);
    }
}
