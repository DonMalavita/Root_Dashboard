<?php
define('DS', DIRECTORY_SEPARATOR);
//------------- Begin Configuratie --------------------------------------------------------
$GLOBALS['config'] = array(

    'app' => array(
        'public_html' => 'http://localhost/Root_Dashboard/public_html/',
        'url' => $_SERVER['DOCUMENT_ROOT'],
        'name' => 'Root_Dashboard',
        'vers' => 1.0,
        'timezone' => 'Europe/Amsterdam',
    ),
    'admin' => array(
        'debug' => true,
        'prod_mode' => 'development', //Current stage
    ),
    'admin_autoloader' => array(
        'debug' => true,
        'verbose' => true,
    ),
    'mysql' => array(
        'db_driver' => 'mysql',
        'db_host' => 'localhost',
        'db_user' => 'root',
        'db_passw' => '',
        'dbname' => 'development',
    ),
    'framework' => array(
        'smarty' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/resources/librarys/',
        'laravel' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/resources/librarys/',
    ),
    'includes' => array(
        'composer-autoload' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/resources/vendor/autoload.php',
        'composer-bin-directorie' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/resources/vendor/bin',
        'config' => str_replace(array('\\','/'), DIRECTORY_SEPARATOR, PROJECT_DIR . '/resources/core/app_conf'),
        'lib' => str_replace(array('\\','/'), DIRECTORY_SEPARATOR, PROJECT_DIR . '/resources/library'),
        'includes-folder' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/resources/app/',
        'error-templates' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/public_html/templates/error/',
        'templates-dir' => $_SERVER['DOCUMENT_ROOT'] . '/Root_Dashboard/public_html/templates/',
    ),
    'cookie-jar' => array(
        'remember' => array(
            'cookie_name' => 'rememberme',
            'cookie_expire' => 604800
        ),
    ),
    'session' => array(
        'encrypt_session' => true,
        'session_name' => 'user',
        'token_name' => 'token'
    ),
    'encryption' => array(
        'key256' => '603deb1015ca71be2b73aef0857d77811f352c073',
        'mode' => 'ecb'
    ),
    'log' => array(
        'max_file_size' => 5000,
        'bestands_prefix' => false,
        'dir' => $_SERVER['DOCUMENT_ROOT'] . '/public_html/logs/',
    )
);
