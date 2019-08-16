<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2019/8/16
 * Email: zhangatle@gmail.com
 */

namespace Zhangatle\Weather;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Weather::class,function (){
            return new Weather(config('services.weather.key'));
        });
        $this->app->alias(Weather::class,'weather');
    }

    public function provides(){
        return [Weather::class,'weather'];
    }
}