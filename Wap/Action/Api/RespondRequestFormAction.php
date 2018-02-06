<?php
namespace Payum\Alipay\Wap\Action\Api;

use Payum\Alipay\Wap\Request\Api\RespondRequestForm;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;

class RespondRequestFormAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request BuildRequestForm
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $form = $this->api->buildRequestForm((array) $details);

        throw new HttpResponse($form);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof RespondRequestForm &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
