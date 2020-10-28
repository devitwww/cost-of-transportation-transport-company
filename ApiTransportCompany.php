<?php

namespace Tpwwswww\CostOfTransportationTransportCompany;

class ApiTransportCompany
{

    public function glavDostavka($from, $to, $weight, $places, $length, $width, $height, $cargoCalculation = 0)
    {
        $from = $this->glavDostavkaCity($from);
        $to = $this->glavDostavkaCity($to);
        $weight = $weight * $places;

        $url = "https://glav-dostavka.ru/api/calc/?method=api_calc&responseFormat=json&depPoint=$from&arrPoint=$to&cargoMest=$places&cargoKg=$weight&cargoL=$length&cargoW=$width&cargoH=$height&cargoCalculation=$cargoCalculation&insure=1&depDoor=0&arrDoor=0";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        $result = json_decode(utf8_decode($response), true);
        $price = $result['price'];

        return $price;
    }


    private function glavDostavkaCity($city)
    {
        $url = "https://glav-dostavka.ru/api/calc/?responseFormat=json&method=api_city";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $results = json_decode(utf8_decode($response), true);
        foreach ($results as $result) {
            if ($result['name'] == $city) {
                return $result['id'];
            }
        }
        return '';
    }

    public function kit($from, $to, $weight, $places, $length, $width, $height)
    {
        $from = $this->kitCity($from);
        $to = $this->kitCity($to);
        $volume = ($width * $length * $height) * $places;
        $weight = $weight * $places;

        $url = "https://tk-kit.com/API.1?f=price_order&I_DELIVER=0&I_PICK_UP=0&WEIGHT=" . $weight . "&VOLUME= " . $volume ."&SZONE=" . $from['szone'] . "&SCODE=" . $from['scode'] . "&RZONE=" . $to['rzone'] ."&RCODE=" . $to['rcode'] . "&PRICE=1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);

        $arr = json_decode($response, true);
        $price = (int) $arr['PRICE']['TOTAL'];

        return $price;
    }

    private function kitCity($city)
    {
        $url = "https://tk-kit.com/API.1?f=get_city_list";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);

        $arr = json_decode($response, true);
        $datas = array();
        foreach ($arr as $k => $v) {
            foreach($v as $jj => $j) {
                array_push($datas, $j);
            }
        }
        foreach ($datas as $data => $dat) {
            if ($dat['NAME'] == $city) {
                $result = array("szone" => $dat['TZONEID'], "scode" => $dat['ID'], "rzone" => $dat['TZONEID'], "rcode" => $dat['ID']);
                return $result;
            }
        }
        return '';
    }

    public function pecom($key, $login, $from, $to, $weight, $places, $length, $width, $height)
    {
        $url = "https://kabinet.pecom.ru/api/v1/calculator/calculateprice/";

        $from = $this->pecomCity($key, $login, $from);
        $to = $this->pecomCity($key, $login, $to);
        $weight = $weight * $places;
        $volume = round(($width * $length * $height)  * $places, 1);

        $array = array(
            "senderCityId" =>  $from,
            "receiverCityId" =>  $to,
            "isInsurance" => false,
            "isInsurancePrice" => 1,
            "isPickUp" => false,
            "isDelivery" => false,
            "Cargos" =>  [[
                "length" =>  $length,
                "width" =>  $width,
                "height" =>  $height,
                "volume" => $volume,
                "sealingPositionsCount" =>  1,
                "weight" =>  $weight,
            ]]
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $login . ":" . $key);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);

        $arr = json_decode($response, true);
        $price = (int) $arr['transfers'][0]['costTotal'];

        return $price;
    }

    private function pecomCity($key, $login, $city, $n=0, $c = '')
    {
        $url = "https://kabinet.pecom.ru/api/v1/branches/findbytitle/";

        $array = array(
            "title" => $city
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $login . ":" . $key);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        $arr = json_decode($response, true);
        $cityId = '';
        if ($arr['success'] == true) {
            $cityId .= $arr['items'][0]['branchId'];
            return $cityId;
        } else {
            return $this->pecomCity($city . ' Восток', 1, $city);
        }

        if ($n == 1) {
            return $this->pecomCity($c . ' Запад', 2);
        }

        if ($n == 2) {
            return '';
        }
        return '';
    }

    public function avtotransit($from, $to, $weight, $places, $length, $width, $height)
    {
        $weight = $weight * $places;
        $volume = ($width * $length * $height) * $places;

        $url = "http://avtotransit.ru/calculator/api.php?CITY_FROM=". $from ."&CITY_TO=". $to ."&PLACE_FROM=storage&PLACE_TO=storage&VOLUME=". $volume ."&WEIGHT=" . $weight ."";
        $response = json_decode(file_get_contents($url), true);

        $price = $response['RESPONSE']['PRICE_TOTAL'];

        return $price;
    }

    public function jde($from, $to, $weight, $places, $length, $width, $height)
    {
        $volume = $width * $length * $height;

        $array = array(
            'from' => $from,
            'to' => $to,
            'weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'quantity' => $places,
            'volume' => $volume,
            'delivery' => 0,
            'pickup' => 0,
            'declared' => 1,
            'smart' => 1,
        );
        $url = "https://api.jde.ru/vD/calculator/price?" . http_build_query($array);
        $response = json_decode(file_get_contents($url), true);

        $price = $response['price'];

        return $price;
    }

    public function dpd($clientNumber, $clientKey, $from, $to, $weight, $places, $length, $width, $height)
    {
        $from = $this->dpdCity($clientNumber, $clientKey, $from);
        $to = $this->dpdCity($clientNumber, $clientKey, $to);
        $weight = $weight * $places;
        $volume = ($width * $length * $height) * $places;

        $client = new SoapClient("http://ws.dpd.ru/services/calculator2?wsdl");
        $data = array('auth' => array('clientNumber' => $clientNumber, 'clientKey' => $clientKey),
            'pickup' => array('cityId' => $from),
            'delivery' => array('cityId' => $to),
            'selfPickup' => true,
            'selfDelivery' => true,
            'weight' => $weight,
            'volume' => $volume
        );
        $arRequest['request'] = $data;
        $ret = $client->getServiceCost2($arRequest);
        $costs = array();
        foreach ($ret->return as $k => $v) {
            array_push($costs, $v->cost);
        }
        $price = (int) min($costs);;

        return $price;
    }

    private function dpdCity($clientNumber, $clientKey, $city)
    {
        $client = new \SoapClient("http://ws.dpd.ru/services/geography2?wsdl");
        $data = array('auth' => array('clientNumber' => $clientNumber, 'clientKey' => $clientKey),
            'cityName' => $city);
        $arRequest['request'] = $data;
        $ret = $client->getParcelShops($arRequest);
        if (is_array($ret->return->parcelShop)) {
            return $ret->return->parcelShop[0]->address->cityId;
        } else {
            return $ret->return->parcelShop->address->cityId;
        }

        return '';
    }

    public function dellin($appKey, $from, $to, $weight, $places, $length, $width, $height)
    {
        $from = $this->dellinCity($from);
        $to = $this->dellinCity($to);

        $url = "https://api.dellin.ru/v1/public/calculator.json";
        $weight = $weight * $places;
        $volume = ($width * $length * $height) * $places;

        $array = array(
            "appKey" => "$appKey",
            "derivalPoint" => "$from",
            "arrivalPoint" => "$to",
            "derivalDoor" => "false",
            "arrivalDoor" => "false",
            "sizedVolume" => "$volume",
            "sizedWeight" => "$weight",
            "quantity" => "$places",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        $response = curl_exec($ch);

        $arr = json_decode($response, true);
        $price = (int) $arr['price'];

        return $price;

    }

    private function dellinCity($city)
    {
        $file = './cities/cities.csv';
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (preg_match("/^($city)/is", $data[1])) {
                    return $data[2];
                }
            }
            fclose($handle);
        }
        return '';
    }

    public function vozovoz($token, $from, $to, $weight, $places, $length, $width, $height)
    {
        $volume = $width * $length * $height;
        $weight = $weight * $places;

        $url = "https://vozovoz.ru/api/?token=". $token;

        $array = array(
            "object" => "price",
            "action" => "get",
            "params" => [
                "cargo"  => [
                    "dimension" => [
                        "quantity" => $places,
                        "volume" => $volume,
                        "weight" => $weight
                    ]
                ],
                "gateway" => [
                    "dispatch" => [
                        "point" => [
                            "location" => $from,
                            "terminal" => "default"
                        ]
                    ],
                    "destination" => [
                        "point" => [
                            "location" => $to,
                            "terminal" => "default"
                        ],
                    ],
                ],
                "insurance" => "1",
            ]
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);

        $arr = json_decode($response, true);
        $price = $arr['response']['basePrice'];

        return $price;
    }
}
