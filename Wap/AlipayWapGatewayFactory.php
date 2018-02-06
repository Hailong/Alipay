<?php
namespace Payum\Alipay\Wap;

use Payum\Alipay\Wap\Action\AuthorizeAction;
use Payum\Alipay\Wap\Action\CancelAction;
use Payum\Alipay\Wap\Action\ConvertPaymentAction;
use Payum\Alipay\Wap\Action\CaptureAction;
use Payum\Alipay\Wap\Action\NotifyAction;
use Payum\Alipay\Wap\Action\RefundAction;
use Payum\Alipay\Wap\Action\StatusAction;
use Payum\Alipay\Wap\Action\Api\RespondRequestFormAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class AlipayWapGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'alipay',
            'payum.factory_title' => 'alipay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.respond_request_form' => new RespondRequestFormAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
