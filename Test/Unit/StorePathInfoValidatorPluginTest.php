<?php

namespace Blackbird\MultiLangStore\Test\Unit;

use Blackbird\MultiLangStore\Plugin\StorePathInfoValidatorPlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class StorePathInfoValidatorPluginTest extends TestCase
{
    private const HH_FR = 'hh_fr';
    private const FR_URL_PREFIX = 'fr';
    private const MAGE_RUN_CODE = 'MAGE_RUN_CODE';
    private const NOT_EXISTING_STORE_CODE = 'NOT_EXISTING_STORE_CODE';
    private StoreInterface $storeMocked;
    private StorePathInfoValidatorPlugin $subjectPathValidator;
    private ScopeConfigInterface $scopeConfigMock;
    private StoreManagerInterface $storeManagerMock;
    private RequestInterface $requestMock;

    private HttpRequest $requestMagentoMock;

    private StorePathInfoValidator $storePathInfoValidator;

    public function setUp(): void
    {
        //Mocking scopeConfig
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        //Mocking storeManager
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        //Mocking requestMagento
        $this->requestMagentoMock = $this->createMock(HttpRequest::class);

        //Mocking request
        $this->requestMock =
            $this->getMockBuilder(RequestInterface::class)
                 ->disableOriginalConstructor()
                 ->addMethods(
                     [
                         'getServer'
                     ])
                 ->onlyMethods(
                     [
                         'getCookie',
                         'getModuleName',
                         'getParam',
                         'getParams',
                         'setParams',
                         'getActionName',
                         'isSecure',
                         'setModuleName',
                         'setActionName'
                     ])->getMock();

        //Mocking StorePathInfoValidator
        $this->storePathInfoValidator = $this->createMock(StorePathInfoValidator::class);

        //Mocking store
        $this->storeMocked = $this->createMock(StoreInterface::class);

        //Subject tested class
        $this->subjectPathValidator = new StorePathInfoValidatorPlugin(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->requestMock
        );
    }

    public function tearDown(): void
    {
        unset($this->storeManagerMock, $this->scopeConfigMock, $this->requestMock, $this->storeMocked, $this->storePathInfoValidator);

        parent::tearDown();
    }

    public function testPathInfoUrlPrefixIsValidBasedOnMageRunCode(): void
    {
        $this->scopeConfigMock->method('getValue')->willReturn(self::FR_URL_PREFIX);
        $this->requestMock->method('getServer')->with(self::MAGE_RUN_CODE)->willReturn(self::HH_FR);
        $this->storeManagerMock->method('getStore')->with(self::HH_FR)->willReturn($this->storeMocked);

        $result = $this->subjectPathValidator->afterGetValidStoreCode(
            $this->storePathInfoValidator,
            null,
            $this->requestMagentoMock
        );

        $this->assertStringContainsString(
            self::FR_URL_PREFIX,
            $result);
    }

    public function testPathInfoReturnNullIfMageRunCodeEmptyString(): void
    {
        $this->requestMock->method('getServer')->with(self::MAGE_RUN_CODE)->willReturn('');
        $this->storeManagerMock->method('getStore')->with(self::HH_FR)->willReturn($this->storeMocked);

        $result = $this->subjectPathValidator->afterGetValidStoreCode(
            $this->storePathInfoValidator,
            null,
            $this->requestMagentoMock
        );

        $this->assertNull($result);
    }

    public function testPathInfoReturnNullBecauseNoSuchEntityException(): void
    {
        $this->scopeConfigMock->method('getValue')->willReturn(self::FR_URL_PREFIX);
        $this->requestMock->method('getServer')->with(self::MAGE_RUN_CODE)->willReturn(self::NOT_EXISTING_STORE_CODE);

        $this->storeManagerMock->method('getStore')->with(self::NOT_EXISTING_STORE_CODE)->willThrowException(
            new NoSuchEntityException());

        $result = $this->subjectPathValidator->afterGetValidStoreCode(
            $this->storePathInfoValidator,
            null,
            $this->requestMagentoMock
        );

        $this->assertNull($result);
    }
}
