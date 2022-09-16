<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:19 下午.
 */

namespace HughCube\Laravel\Package;

use HughCube\Laravel\ServiceSupport\Manager as ServiceSupportManager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container as ContainerContract;

/**
 * @property callable|ContainerContract|null $container
 * @property callable|Repository|null $config
 */
class Manager extends ServiceSupportManager
{
    protected function makeDriver(array $config): Client
    {
        return new Client($config);
    }

    protected function getPackageFacadeAccessor(): string
    {
        return Package::getFacadeAccessor();
    }

    public function getDriversConfigKey(): string
    {
        return 'clients';
    }
}
