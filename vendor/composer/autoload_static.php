<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit281eaf3ef8c4cf2ff5625d8990e25437
{
    public static $files = array (
        '535cf38403fe1a8c6aea5654339f2510' => __DIR__ . '/..' . '/meta-box/meta-box/meta-box.php',
        '687e88d103d3c7b7e29ca5e836cb2272' => __DIR__ . '/..' . '/meta-box/mb-settings-page/mb-settings-page.php',
        'be497f88b773aa5c9974da46821effab' => __DIR__ . '/..' . '/meta-box/mb-admin-columns/mb-admin-columns.php',
        'd46e9eb4d08a138d32890bfd33c08c32' => __DIR__ . '/..' . '/meta-box/meta-box-columns/meta-box-columns.php',
        'd625401fcd6c5ef99e9ddf76eb29ba78' => __DIR__ . '/..' . '/meta-box/meta-box-conditional-logic/meta-box-conditional-logic.php',
        'e689b9619e9752623f82a67f0b829350' => __DIR__ . '/..' . '/meta-box/meta-box-group/meta-box-group.php',
        '3cdaf8a7feac7dab25a504ea4957b65a' => __DIR__ . '/..' . '/woocommerce/action-scheduler/action-scheduler.php',
    );

    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MetaBox\\' => 8,
        ),
        'J' => 
        array (
            'Jelix\\IniFile\\' => 14,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MetaBox\\' => 
        array (
            0 => __DIR__ . '/..' . '/meta-box/meta-box/src',
        ),
        'Jelix\\IniFile\\' => 
        array (
            0 => __DIR__ . '/..' . '/jelix/inifile/lib',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit281eaf3ef8c4cf2ff5625d8990e25437::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit281eaf3ef8c4cf2ff5625d8990e25437::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit281eaf3ef8c4cf2ff5625d8990e25437::$classMap;

        }, null, ClassLoader::class);
    }
}
