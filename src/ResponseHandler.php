<?php

namespace tinkers\ccavenue;

class ResponseHandler
{

    /**
     * Decodes and returns response array
     *
     * @param $encodedData
     * @param CCAvenue $cCAvenue
     * @return mixed
     */
    public static function response ($encodedData, CCAvenue $cCAvenue)
    {
        $decodedString = Crypto::decrypt($encodedData, $cCAvenue->getWorkingKey());

        parse_str($decodedString, $response);

        return $response;

    }

    public static function paymentStatus ($encodedData, CCAvenue $cCAvenue)
    {
        $decodedString = Crypto::decrypt($encodedData, $cCAvenue->getWorkingKey());

        parse_str($decodedString, $response);

        return $response['order_status'];
    }

}