<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Rss;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\SalesRule\Model\Rss\Discounts;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscountsTest extends TestCase
{
    /**
     * @var \Magento\SalesRule\Block\Rss\Discounts
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var Store|MockObject
     */
    protected $storeModel;

    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts|MockObject
     */
    protected $discounts;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $rssBuilderInterface;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestInterface;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts|MockObject
     */
    protected $rssModel;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneInterface;

    protected function setUp(): void
    {
        $this->storeManagerInterface = $this->createMock(StoreManagerInterface::class);
        $this->requestInterface = $this->createMock(RequestInterface::class);
        $this->rssBuilderInterface = $this->createMock(UrlBuilderInterface::class);
        $this->urlBuilderInterface = $this->createMock(UrlInterface::class);
        $this->scopeConfigInterface = $this->createMock(ScopeConfigInterface::class);
        $this->timezoneInterface = $this->createMock(TimezoneInterface::class);
        $this->rssModel = $this->createPartialMock(Discounts::class, [
                '__wakeup',
                'getDiscountCollection'
            ]);
        $this->storeModel = $this->createPartialMock(Store::class, [
                '__wakeUp',
                'getId',
                'getWebsiteId',
                'getName',
                'getFrontendName'
            ]);

        $this->storeManagerInterface->expects($this->any())->method('getStore')
            ->will($this->returnValue($this->storeModel));
        $this->storeModel->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\SalesRule\Block\Rss\Discounts::class,
            [
                'storeManager' => $this->storeManagerInterface,
                'rssUrlBuilder' => $this->rssBuilderInterface,
                'urlBuilder' => $this->urlBuilderInterface,
                'request' => $this->requestInterface,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssModel' => $this->rssModel,
                'localeDate' => $this->timezoneInterface
            ]
        );
    }

    public function testGetRssData()
    {
        $ruleData = [
            'to_date' => '12/12/14',
            'from_date' => '12/12/14',
            'coupon_code' => '1234567',
            'description' => 'Rule Description',
            'name' => 'Rule Name',
        ];
        $rssData = [
            'title' => 'Frontend Name - Discounts and Coupons',
            'description' => 'Frontend Name - Discounts and Coupons',
            'link' => 'http://rss.magento.com/discount',
            'charset' => 'UTF-8',
            'language' => 'en_US',
            'entries' => [
                'title' => 'Rule Name',
                'link' => 'http://rss.magento.com',
                'description' => [
                        'description' => 'Rule Description',
                        'start_date' => '12/12/14',
                        'end_date' => '12/12/14',
                        'coupon_code' => '1234567',
                    ],
            ],
        ];
        $rssUrl = 'http://rss.magento.com/discount';
        $url = 'http://rss.magento.com';

        $ruleModel =  $this->createPartialMock(Rule::class, [
                '__wakeup',
                'getCouponCode',
                'getToDate',
                'getFromDate',
                'getDescription',
                'getName'
            ]);

        $this->storeModel->expects($this->once())->method('getWebsiteId')->will($this->returnValue(1));
        $this->storeModel->expects($this->never())->method('getName');
        $this->storeModel->expects($this->atLeastOnce())->method('getFrontendName')->willReturn('Frontend Name');

        $this->requestInterface->expects($this->any())->method('getParam')->will($this->returnValue(1));
        $this->urlBuilderInterface->expects($this->any())->method('getUrl')->will($this->returnValue($url));
        $this->rssBuilderInterface->expects($this->any())->method('getUrl')->will($this->returnValue($rssUrl));
        $this->scopeConfigInterface->expects($this->any())->method('getValue')->will($this->returnValue('en_US'));
        $ruleModel->expects($this->any())->method('getCouponCode')->will($this->returnValue($ruleData['coupon_code']));
        $ruleModel->expects($this->any())->method('getToDate')->will($this->returnValue($ruleData['to_date']));
        $ruleModel->expects($this->once())->method('getFromDate')->will($this->returnValue($ruleData['from_date']));
        $ruleModel->expects($this->once())->method('getDescription')
            ->will($this->returnValue($ruleData['description']));
        $ruleModel->expects($this->once())->method('getName')->will($this->returnValue($ruleData['name']));
        $this->rssModel->expects($this->any())->method('getDiscountCollection')
            ->will($this->returnValue([$ruleModel]));
        $this->timezoneInterface->expects($this->any())->method('formatDateTime')->will($this->returnValue('12/12/14'));

        $data = $this->block->getRssData();

        $this->assertEquals($rssData['title'], $data['title']);
        $this->assertEquals($rssData['description'], $data['description']);
        $this->assertEquals($rssData['link'], $data['link']);
        $this->assertEquals($rssData['charset'], $data['charset']);
        $this->assertEquals($rssData['language'], $data['language']);
        $this->assertEquals($rssData['entries']['title'], $data['entries'][0]['title']);
        $this->assertEquals($rssData['entries']['link'], $data['entries'][0]['link']);
        $this->assertContains($rssData['entries']['description']['description'], $data['entries'][0]['description']);
        $this->assertContains($rssData['entries']['description']['start_date'], $data['entries'][0]['description']);
        $this->assertContains($rssData['entries']['description']['end_date'], $data['entries'][0]['description']);
        $this->assertContains($rssData['entries']['description']['coupon_code'], $data['entries'][0]['description']);
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(0, $this->block->getCacheLifetime());
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isAllowed
     */
    public function testIsAllowed($isAllowed)
    {
        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')->will($this->returnValue($isAllowed));
        $this->assertEquals($isAllowed, $this->block->isAllowed());
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testGetFeeds()
    {
        $feedData = [
            'label' => 'Coupons/Discounts',
            'link' => 'http://rss.magento.com/discount',
        ];
        $this->rssBuilderInterface->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($feedData['link']));

        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')->will($this->returnValue(true));
        $this->assertEquals($feedData, $this->block->getFeeds());
    }
}
