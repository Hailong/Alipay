<?php
namespace Payum\Alipay\Wap\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Alipay\Wap\Request\Api\CheckSign;
use Payum\Alipay\Wap\Request\Api\RespondRequestForm;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if (isset($httpRequest->query['trade_no'])) {
            $details->replace($httpRequest->query);

            $this->gateway->execute(new CheckSign($details));

            return;
        }

        $details['return_url'] = $request->getToken()->getTargetUrl();

        if (empty($details['notify_url']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $details['notify_url'] = $notifyToken->getTargetUrl();
        }

        $this->gateway->execute(new RespondRequestForm($details));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
