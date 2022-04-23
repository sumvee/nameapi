<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class NameAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $url = env('NAMES_API_URL');
        $responses = [];
        while (count($responses) < 10){
            $responses = array_merge($responses, Http::pool(fn(Pool $pool) => [
                $pool->get($url),
                $pool->get($url),

            ]));
            sleep(.1);
        }
        $all = $this->getMappedData($responses);

        usort($all, [$this, 'sortByLastName']);
        $xml = $this->getAsXML($all);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }


    /**
     * @param $first
     * @param $second
     * @return int
     */
    function sortByLastName($first, $second): int
    {
        $a = $first['last_name'];
        $b = $second['last_name'];
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? -1 : 1;
    }

    /**
     * Convert an array to XML
     * @param array $array
     * @param SimpleXMLElement $xml
     */
    function arrayToXml(array $array, SimpleXMLElement &$xml)
    {

        foreach ($array as $i => $item) {
            if (is_int($i)) {
                $i = "element";
            }
            if (is_array($item)) {
                $elementTitle = $xml->addChild($i);
                $this->arrayToXml($item, $elementTitle);
            } else {
                $xml->addChild($i, $item);
            }
        }
    }

    /**
     * @param $responses
     * @return array
     */
    public function getMappedData($responses): array
    {

        $all = [];
        foreach ($responses as $response) {
            $array = $response->json()['results'][0];
            $out = [];
            $out['full_name'] = implode(' ', $array['name']);
            $out['phone'] = $array['phone'];
            $out['email'] = $array['email'];
            $out['country'] = $array['location']['country'];
            $out['last_name'] = $array['name']['last'];
            $all[] = $out;
        }
        return $all;
    }

    /**
     * @param array $all
     * @return bool|string
     */
    public function getAsXML(array $all)
    {
        foreach ($all as &$a) {
            unset($a['last_name']);
        }
        $xml = new SimpleXMLElement('<root/>');
        $this->arrayToXml($all, $xml);
        return  $xml->saveXML();
    }
}
