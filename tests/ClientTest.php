<?php
namespace PayU\Alu\Test;

use PayU\Alu\Client;
use PayU\Alu\Component\Request;
use PayU\Alu\Component\Response;
use PayU\Alu\HashService;
use PayU\Alu\HTTPClient;
use PayU\Alu\MerchantConfig;
use PayU\Alu\Parser\ThreeDSecureResponseParser;
use PayU\Alu\Platform;
use PayU\Alu\Parser\PaymentResponseParser;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /** @var Client */
    private $client;

    /** @var MerchantConfig */
    private $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $hashServiceMock;

    public function setUp()
    {
        $this->config = new MerchantConfig('CC5857', 'SECRET_KEY', Platform::ROMANIA);
        $this->client = new Client($this->config);

        $this->requestMock = $this->getMockBuilder('\PayU\Alu\Component\Request')
            ->disableOriginalConstructor()
            ->getMock();


        $this->hashServiceMock = $this->getMockBuilder('\PayU\Alu\HashService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testPay()
    {
        $httpClientMock = $this->getMockBuilder('\PayU\Alu\HTTPClient')
            ->disableOriginalConstructor()
            ->getMock();

        $httpClientMock->expects($this->once())
            ->method("post")
            ->willReturn("DUMMY_RESPONSE");

        $this->hashServiceMock->expects($this->once())
            ->method('sign')
            ->willReturn(array());

        $responseMock = $this->getMockBuilder('\PayU\Alu\Component\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentResponseParserMock = $this->getMockBuilder('\PayU\Alu\Parser\PaymentResponseParser')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentResponseParserMock->expects($this->once())
            ->method("parse")
            ->willReturn($responseMock);

        $reflectionClient = new \ReflectionObject($this->client);

        $httpClientProperty = $reflectionClient->getProperty("httpClient");
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->client, $httpClientMock);

        $hashServiceProperty = $reflectionClient->getProperty("hashService");
        $hashServiceProperty->setAccessible(true);
        $hashServiceProperty->setValue($this->client, $this->hashServiceMock);

        $paymentResponseParserProperty= $reflectionClient->getProperty("paymentResponseParser");
        $paymentResponseParserProperty->setAccessible(true);
        $paymentResponseParserProperty->setValue($this->client, $paymentResponseParserMock);

        $payMethod = $reflectionClient->getMethod("pay");
        $this->assertInstanceOf(
            '\PayU\Alu\Component\Response',
            $payMethod->invokeArgs($this->client, array($this->requestMock))
        );
    }

    public function testHandleThreeDSReturnResponse()
    {
        $threeDSecureResponseParserMock = $this->getMockBuilder('\PayU\Alu\Parser\ThreeDSecureResponseParser')
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder('\PayU\Alu\Component\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $threeDSecureResponseParserMock->expects($this->once())
            ->method("parse")
            ->willReturn($responseMock);

        $reflectionClient = new \ReflectionObject($this->client);
        $threeDSecureResponseParserProperty = $reflectionClient->getProperty("threeDSecureResponseParser");
        $threeDSecureResponseParserProperty->setAccessible(true);
        $threeDSecureResponseParserProperty->setValue($this->client, $threeDSecureResponseParserMock);

        $handleThreeDSReturnResponse = $reflectionClient->getMethod("handleThreeDSReturnResponse");
        $handleThreeDSReturnResponse->invokeArgs($this->client, array(array()));
    }
}
