<?php
/**
 * Created by sqiu.
 * CreateTime: 14-1-2 上午2:57
 *
 */
namespace Omnipay\Alipay\Message;

use DOMDocument;
use Omnipay\Mollie\Message\AbstractRequest;

class WapExpressAuthorizeRequest extends BaseAbstractRequest
{

    protected $endpoint = 'http://wappaygw.alipay.com/service/rest.htm';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->validate(
            'notify_url',
            'return_url',
            'seller_email',
            'out_trade_no',
            'subject',
            'total_fee',
            'cancel_url'
        );
        $req_data     = sprintf(
            '<direct_trade_create_req><notify_url>%s</notify_url><call_back_url>%s</call_back_url><seller_account_name>%s</seller_account_name><out_trade_no>%s</out_trade_no><subject>%s</subject><total_fee>%s</total_fee><merchant_url>%s</merchant_url></direct_trade_create_req>',
            $this->getNotifyUrl(),
            $this->getReturnUrl(),
            $this->getSellerEmail(),
            $this->getOutTradeNo(),
            $this->getSubject(),
            $this->getTotalFee(),
            $this->getCancelUrl()
        );
        $data         = array(
            "service"        => "alipay.wap.trade.create.direct",
            "partner"        => $this->getPartner(),
            "sec_id"         => $this->getSignType(),
            "format"         => 'xml',
            "v"              => '2.0',
            "req_id"         => microtime(true) . '',
            "req_data"       => $req_data,
            "_input_charset" => $this->getInputCharset()
        );
        $data['sign'] = $this->getParamsSignature($data);
        return $data;
    }

    function getKey()
    {
        return $this->getParameter('key');
    }

    function setKey($value)
    {
        return $this->setParameter('key', $value);
    }

    function getInputCharset()
    {
        return $this->getParameter('input_charset');
    }

    function setInputCharset($value)
    {
        return $this->setParameter('input_charset', $value);
    }

    function getSignType()
    {
        return $this->getParameter('sign_type');
    }

    function setSignType($value)
    {
        return $this->setParameter('sign_type', $value);
    }

    function getPartner()
    {
        return $this->getParameter('partner');
    }

    function setPartner($value)
    {
        return $this->setParameter('partner', $value);
    }

    function getTotalFee()
    {
        return $this->getParameter('total_fee');
    }

    function setTotalFee($value)
    {
        return $this->setParameter('total_fee', $value);
    }

    function getSubject()
    {
        return $this->getParameter('subject');
    }

    function setSubject($value)
    {
        return $this->setParameter('subject', $value);
    }

    function getOutTradeNo()
    {
        return $this->getParameter('out_trade_no');
    }

    function setOutTradeNo($value)
    {
        return $this->setParameter('out_trade_no', $value);
    }

    function getSellerEmail()
    {
        return $this->getParameter('seller_email');
    }

    function setSellerEmail($value)
    {
        return $this->setParameter('seller_email', $value);
    }

    function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }

    function setNotifyUrl($value)
    {
        return $this->setParameter('notify_url', $value);
    }

    function getReturnUrl()
    {
        return $this->getParameter('return_url');
    }

    function setReturnUrl($value)
    {
        return $this->setParameter('return_url', $value);
    }

    function getCancelUrl()
    {
        return $this->getParameter('cancel_url');
    }

    function setCancelUrl($value)
    {
        return $this->setParameter('cancel_url', $value);
    }

    function sendData($data)
    {
        $responseText = $this->httpClient->post($this->endpoint, array(), $this->getData())->send()->getBody(true);
        //die($responseText);
        $responseData = $this->parseResponse($responseText);
        //var_dump($responseData);
        return $this->response = new WapExpressAuthorizeResponse($this, $responseData);
    }

    function parseResponse($str_text)
    {
        $str_text   = urldecode($str_text); //URL转码
        $para_split = explode('&', $str_text);
        $data       = array();
        foreach ($para_split as $item) {
            $nPos       = strpos($item, '=');
            $nLen       = strlen($item);
            $key        = substr($item, 0, $nPos);
            $value      = substr($item, $nPos + 1, $nLen - $nPos - 1);
            $data[$key] = $value;
        }
        if (!empty ($data['res_data'])) {
            //            if ($this->getSignType == '0001') {
            //                $data['res_data'] = rsaDecrypt($data['res_data'], $this->alipay_config['private_key_path']);
            //            }
            //token从res_data中解析出来（也就是说res_data中已经包含token的内容）
            $doc = new DOMDocument();
            $doc->loadXML($data['res_data']);
            $data['request_token'] = $doc->getElementsByTagName("request_token")->item(0)->nodeValue;
        }
        return $data;
    }
}