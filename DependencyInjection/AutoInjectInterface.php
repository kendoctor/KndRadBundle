<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 下午8:30
 */

namespace Knd\Bundle\RadBundle\DependencyInjection;


interface AutoInjectInterface {
    public static function getConstructorParameters();
}