<?php

require_once __DIR__.'/../vendor/autoload.php';

use Demo\OAuth2\Client;
use Silex\Provider\TwigServiceProvider;

$env = getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production';
$ini_config = parse_ini_file(__DIR__.'/config.ini', TRUE);
$config = $ini_config[$env];

$app = new Silex\Application();

$app['debug'] = true;
$app['authorize_url']    = $config['authorize_url'];
$app['redirect_url']     = $config['redirect_url'];
$app['token_url']        = $config['token_url'];
$app['auth_secret']      = $config['auth_secret'];
$app['auth_token']       = $config['auth_token'];
$app['api_url']          = $config['api_url'];
$app['base_url']          = $config['base_url'];

if ($app['auth_token'] === 'the_auth_token' || $app['auth_secret'] === 'the_auth_secret') {
    throw new \RuntimeException('You need to change your auth_secret and auth_token values in src/config.ini to those created by the oauth server.');
}

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
    'twig.options' => array('cache' => __DIR__.'/../cache'),
));

$app['oauth'] = $app->share(function() use ($app) {
    $config = array(
            'base_uri' => $app['base_url'],
            'client_id' => $app['auth_token'],
            'client_secret' => $app['auth_secret'],
            'access_token_uri' => $app['token_url']
    );

    return new Client($config);
});


$app->register(new Silex\Provider\SessionServiceProvider());

return $app;