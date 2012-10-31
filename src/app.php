<?php
$app = require __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Render the link to the oauth2 authorization page
 */
$app->get('/', function() use ($app){
    $url = sprintf($app['authorize_url'], $app['auth_token'], $app['redirect_url']);
    return $app['twig']->render('index.html.twig', array('token' => $app['session']->get('token'), 'url' => $url));
});


/**
 * The redirect_uri. Use the OAuth object to get an access token.
 */
$app->get('/back', function() use ($app) {

    $code = $app['request']->get('code');

    try {
        $oauth = $app['oauth'];
        $redirect = $app['redirect_url'];
        $url = $app['token_url'] . sprintf('?grant_type=authorization_code&client_secret=%s&client_id=%s&code=%s&redirect_uri=%s', $app['auth_secret'], $app['auth_token'], $code, urlencode($redirect));
        $access_token_info = $oauth->getAccessToken($url);
    } catch (\OAuthException $e) {
        print_r(json_decode($e->lastResponse));
        die();
    }

    // the response doesn't contain the proper array from the OAuth documentation,  the first
    // key holds the raw json response
    $result = json_decode(array_keys($access_token_info)[0], true);
    
    if (!isset($result['access_token'])) {
        throw new \RuntimeException('Error retrieving access token');
    }
    
    // access token retrieved, redirect back to home
    $app['session']->set('token', $result['access_token']);
    return new RedirectResponse('/');
});


$app->get('/request', function() use ($app) {
    
    $oauth = $app['oauth'];
    $oauth->fetch($app['api_url'] . '?access_token=' . $app['session']->get('token'));
    $response = new Response($oauth->getLastResponse());
    $response->headers->set('Content-Type', 'application/json');
     
    return $response;
});

return $app;