<?php

/*
 * This file is part of the zhangatle/weather.
 *
 * (c) zhangatle <zhangatle@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Zhangatle\Weather\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Zhangatle\Weather\Exceptions\InvalidArgumentException;
use Zhangatle\Weather\Weather;
use Mockery\Matcher\AnyArgs;
use Zhangatle\Weather\Exceptions\HttpException;

class WeatherTest extends TestCase
{
    public function testGetWeather()
    {
        // json
        $response = new Response(200, [], '{"success": true}');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'output' => 'json',
                'extensions' => 'base',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getWeather('深圳'));

        // xml
        $response = new Response(200, [], '<hello>content</hello>');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'extensions' => 'all',
                'output' => 'xml',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $w->getWeather('深圳', 'forecast', 'xml'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs()) // 由于上面的用例已经验证过参数传递，所以这里就不关心参数了。
            ->andThrow(new \Exception('request timeout')); // 当调用 get 方法时会抛出异常。

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        // 接着需要断言调用时会产生异常。
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $w->getWeather('深圳');
    }

    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');
        $this->assertInstanceOf(ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');

        $this->assertNull($w->getHttpClient()->getConfig('timeout'));
        // 设置参数
        $w->setGuzzleOptions(['timeout' => 5000]);

        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }

    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');

        // 断言会抛出此异常类
        $this->expectException(InvalidArgumentException::class);
        // 断言异常消息为 'Invalid type value(base/all): foo'
        $this->expectExceptionMessage('Invalid type value(live/forecast): foo');
        $w->getWeather('深圳', 'foo');
        $this->fail('Failed to assert getWeather throw exception with invalid argument');
    }

    // 检查$format参数
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');
        // 断言会抛出此异常类
        $this->expectException(InvalidArgumentException::class);
        // 断言异常消息为 'Invalid response format: array'
        $this->expectExceptionMessage('Invalid response format: array');
        $w->getWeather('深圳', 'forecast', 'array');
        $this->fail('Failed to assert getWeather throw exception with invalid argument');
    }

    public function testGetLiveWeather()
    {
        // 将 getWeather 接口模拟为返回固定内容，以测试参数传递是否正确
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->expects()->getWeather('深圳', 'live', 'json')->andReturn(['success' => true]);

        // 断言正确传参并返回
        $this->assertSame(['success' => true], $w->getLiveWeather('深圳'));
    }

    public function testGetForecastsWeather()
    {
        // 将 getWeather 接口模拟为返回固定内容，以测试参数传递是否正确
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->expects()->getWeather('深圳', 'forecast', 'json')->andReturn(['success' => true]);

        // 断言正确传参并返回
        $this->assertSame(['success' => true], $w->getForecastsWeather('深圳'));
    }
}
