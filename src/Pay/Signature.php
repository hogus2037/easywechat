<?php

namespace EasyWeChat\Pay;

use Psr\Http\Message\RequestInterface;

class Signature
{
    public function __construct(protected Merchant $merchant)
    {
    }

    public function createHeader(RequestInterface $request): string
    {
        $body = '';
        $nonce = \uniqid('nonce');
        $timestamp = \time();

        if ($request->getBody()->isSeekable()) {
            $body = $request->getBody()->getContents();
            $request->getBody()->rewind();
        }

        $message = $request->getMethod()."\n".
                   $request->getRequestTarget()."\n".
                   $timestamp."\n".
                   $nonce."\n".
                   $body."\n";

        $signature = \base64_encode(\openssl_sign($message, $sign, $this->merchant->getPrivateKey(), 'sha256WithRSAEncryption'));

        return sprintf(
            'WECHATPAY2-SHA256-RSA2048 %s',
            sprintf(
                'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
                $this->merchant->getMerchantId(),
                $nonce,
                $timestamp,
                $this->merchant->getCertificateSerialNumber(),
                $signature
            )
        );
    }
}
