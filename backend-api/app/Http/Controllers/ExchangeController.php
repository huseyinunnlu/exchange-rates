<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleXMLElement;

class ExchangeController extends Controller
{
    public function fetchData($date)
    {
        $result = [
            'success' => false,
            'date'=>'',
            'bultenNo'=>'',
            'results' => array(),
        ];

        $date= (!empty($date) ? $date : 'today');
        if ($date=='today')
        {
            $url ='https://www.tcmb.gov.tr/kurlar/today.xml';
        }
        else
        {
            $yil=substr($date,4,4);
            $ay=substr($date,2,2);
            $url = 'https://www.tcmb.gov.tr/kurlar/'.$yil.$ay.'/'.$date.'.xml';
        }

        try {
            $sxe=true;
            $xml = simplexml_load_file($url);
        }catch (\Exception $e) {
            $sxe=false;
        }
        if (false === $sxe) {
            array_unshift($result['results'],
                ['message'=>'Date incorrect.']
            );
            return response()->json($result);
        }
        else{
            $count = 1;
            $result['date'] = $date;
            $result['success'] = true;
            $resutl['bultenNo'] = $xml->attributes()->Bulten_No;
            foreach ($xml->children() as $children) {
                array_push($result['results'],[
                    'Row'=>$count,
                    'Unit'=>(string) $children->Unit,
                    'Code'=>(string) $children->attributes()->CurrencyCode,
                    'CurrencyName'=>(string) $children->CurrencyName,
                    'ForexBuying'=>(string) $children->ForexBuying,
                    'ForexSelling'=>(string) $children->ForexSelling,
                    'BanknoteBuying'=>(string) $children->BanknoteBuying,
                    'BanknoteSelling'=>(string) $children->BanknoteSelling,
                ]);
                $count++;
            }
            return $result; 
        }  
    }
}
