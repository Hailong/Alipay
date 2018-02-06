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

    public function checkSign(array $fields)
    {
        $result = $this->alipayTradeService->check($fields);

        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码
            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号

            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

            //支付宝交易号

            $trade_no = htmlspecialchars($_GET['trade_no']);
                
            // echo "验证成功<br />外部订单号：".$out_trade_no;

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            // echo "验证失败";
        }

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
