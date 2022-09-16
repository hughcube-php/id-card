<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/18
 * Time: 10:31 下午.
 */

namespace HughCube\Laravel\Package;

use HughCube\Laravel\ServiceSupport\LazyFacade;
use Illuminate\Support\Str;

/**
 * Class Package.
 *
 * @method static Client driver(string $name = null)
 *
 * @see \HughCube\Laravel\Package\Manager
 * @see \HughCube\Laravel\Package\ServiceProvider
 */
class Package extends LazyFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return lcfirst(Str::afterLast(static::class, "\\"));
    }

    /**
     * @inheritDoc
     */
    protected static function registerServiceProvider($app)
    {
        $app->register(ServiceProvider::class);
    }
}
