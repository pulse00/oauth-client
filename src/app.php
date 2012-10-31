<?php
$app = require __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Demo\OAuth2\Client;


/**
 * Render the link to the oauth2 authorization page
 */
$app->get('/', function() use ($app){
    $url = sprintf( $app['base_url'] . $app['authorize_url'], $app['auth_token'], $app['redirect_url']);
    return $app['twig']->render('index.html.twig', array('token' => $app['session']->get('session')['access_token'], 'url' => $url));
});


/**
 * The redirect_uri. Use the OAuth object to get an access token.
 */
$app->get('/back', function() use ($app) {

    $oauth = $app['oauth'];
    $oauth->setVariable('code', $app['request']->get('code'));
    $token = $oauth->getSession();

    if (!$token) {
        throw new \RuntimeException('Error retrieving access token');
    }

    $app['session']->set('session', $token);
    return new RedirectResponse('/');
});


$app->get('/request', function() use ($app) {

    $oauth = $app['oauth'];
    $result = $oauth->api($app['api_url'] . '?access_token=' . $app['session']->get('session')['access_token']);

    $response = new Response(json_encode($result));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
});

return $app;