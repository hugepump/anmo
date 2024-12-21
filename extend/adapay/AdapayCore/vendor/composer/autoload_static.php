<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc58d291072fac211c36774780be871de
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\Mqtt\\' => 15,
            'Workerman\\' => 10,
        ),
        's' =>
            array (
                'sskaje\\mqtt\\' => 12,
            ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\Mqtt\\' => 
        array (
            0 => __DIR__ . '/vendor' . '/workerman/mqtt/src',
        ),
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/vendor',
        ),
        'sskaje\\mqtt\\' =>
            array (
                0 => __DIR__ . '/vendor' . '/sskaje/mqtt',
            ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc58d291072fac211c36774780be871de::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc58d291072fac211c36774780be871de::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
