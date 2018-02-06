<?php
namespace Payum\Alipay\Wap;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    protected $alipayTradeService;

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;

        require __DIR__ . '/../AlipaySDK/alipay.trade.wap.pay-PHP-UTF-8/wappay/service/AlipayTradeService.php';

        $this->alipayTradeService = new \AlipayTradeService([
            'gatewayUrl'           => $options['gateway_url'],
            'app_id'               => $options['app_id'],
            'merchant_private_key' => file_get_contents($options['rsa_private_key']),
            'alipay_public_key'    => file_get_contents($options['rsa_public_key']),
            'charset'              => $options['charset'],
            'sign_type'            => $options['sign_type'],
        ]);
    }

    public function buildRequestForm(array $fields)
    {
        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($fields['body']);
        $payRequestBuilder->setSubject($fields['subject']);
        $payRequestBuilder->setOutTradeNo($fields['out_trade_no']);
        $payRequestBuilder->setTotalAmount($fields['total_amount']);
        $payRequestBuilder->setTimeExpress($fields['timeout_express']);

        ob_start();

        $result = $this->alipayTradeService->wapPay(
            $payRequestBuilder,
            $fields['return_url'],
            $fields['notify_url']
        );

        ob_end_clean();

        return $result;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, array $fields)
    {
        $headers = [];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'http://sandbox.example.com' : 'http://example.com';
    }
}
