<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2019/8/16
 * Email: zhangatle@gmail.com
 */

namespace Zhangatle\Weather;


use GuzzleHttp\Client;
use Zhangatle\Weather\Exceptions\HttpException;
use Zhangatle\Weather\Exceptions\InvalidArgumentException;

class Weather
{
    protected $key;
    protected $guzzleOptions = [];

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getWeather($city, string $type = 'base', string $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        if(!\in_array(\strtolower($format),['xml','json'])){
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }
        if(!\in_array(\strtolower($type),['base','all'])){
            throw new InvalidArgumentException('Invalid type value(base/all): '.$type);
        }
        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);
        try{
            $response = $this->getHttpClient()->get($url,['query'=>$query])->getBody()->getContents();
            return 'json' === $format ? \json_decode($response,true) : $response;
        }catch (\Exception $e){
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }
}