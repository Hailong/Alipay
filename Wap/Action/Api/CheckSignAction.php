<?php
namespace Payum\Alipay\Wap\Action\Api;

use Payum\Alipay\Wap\Request\Api\CheckSign;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class CheckSignAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request CheckSign
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $result = $this->api->checkSign($_GET);

        $details->replace([
            'VALID_SIGN' => $result,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CheckSign &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
