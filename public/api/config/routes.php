<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->get('/api/public/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/public/info',App\Handler\InfoPageHandler::class);
    $app->get('/api/public/ping', App\Handler\PingHandler::class, 'api.ping');
    $app->get('/api/public/table/{desiredValue:\d+}', App\Handler\DisplayTablePageHandler::class);
    $app->get('/api/public/table/[{desiredColumn:firstname|lastname|email}={desiredValue}]', App\Handler\DisplayTablePageHandler::class);
    $app->post('/api/public/table/firstname={firstname}&lastname={lastname}&email=[{email}]', App\Handler\DisplayTablePageHandler::class);
    $app->patch('/api/public/table/{id:\d+}&{desiredColumn:firstname|lastname|email}={desiredValue}',App\Handler\DisplayTablePageHandler::class);
    $app->put('/api/public/table/{id:\d+}&{desiredColumn0}={desiredValue0}&{desiredColumn1}={desiredValue1}&{desiredColumn2}={desiredValue2}',App\Handler\DisplayTablePageHandler::class);
    $app->delete('/api/public/table/{desiredValue:\d+}',App\Handler\DisplayTablePageHandler::class);
};