<?php
namespace Blackbird\MultiLangStore\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class StoreSwitcherLabel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public const SPECIFIC_STORE_CODE_LABEL_XML_PATH = 'web/url/specific_store_code_label';
    public function __construct
    (
        private ScopeConfigInterface $scopeConfig
    )
    {

    }

    public function getStoreLabelConfig($storeId): string
    {
        return $this->scopeConfig->getValue(
            self::SPECIFIC_STORE_CODE_LABEL_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? '';
    }
}
