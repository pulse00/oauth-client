<?php

$app = require __DIR__.'/bootstrap.php';

$app->get('/', function() use ($app){

    if (null === $token = $app['session']->get('token')) {
        return $app->redirect('/authorize');
    }

    return $app['twig']->render('index.html.twig', array('token' => $app['auth_token']));
});


$app->get('/authorize', function() use ($app) {

    $url = 'http://playfm3.local/app_dev.php/oauth/v2/auth?client_id=' . $app['auth_token'] . '&redirect_uri=' . $app['redirect_url'] . '&response_type=code';

    return $app['twig']->render('authorize.html.twig', array(
            'url' => $url
    ));
});

$app->get('/back', function() use ($app) {

    $code = $app['request']->get('code');

//     http://acme.localhost/app_dev.php/oauth/v2/token?client_id=4f8e5bb57f8b9a0816000000_1xwgejzp1e3o8sgosc884cgoko44wgg4gc0s84ckw0c0sk4c4s&client_secret=147v1qcgxvuscg4owg4480ww484kc0ow0cwgkw0c4g4g8oowkc&grant_type=authorization_code&redirect_uri=http%3A%2F%2Fwww.google.com&code=6c7136745d8556650cb5e0d5cd53029c925aae72


    try {
        $oauth = new \OAuth($app['auth_token'], $app['auth_secret'], OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $oauth->enableDebug();
        $oauth->setToken($code, $app['auth_secret']);
        $access_token_info = $oauth->getAccessToken($app['token_url']);
    } catch (\OAuthException $e) {
        var_dump($e->lastResponse);
    }

    die();
    return $app['twig']->render('authorize.html.twig', array(
            'url' => $url
    ));

});

$app->get('/success', function() use ($app) {

});

return $app;