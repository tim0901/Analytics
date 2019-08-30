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

    //Default/Legacy routing. Info can probably be deleted.
    $app->get('/api/public/info',App\Handler\InfoPageHandler::class);
    $app->get('/api/public/ping', App\Handler\PingHandler::class, 'api.ping');

    //Creating/deleting database
    $app->route('/api/public/createDatabase/',App\Handler\DatabaseCreatorPageHandler::class);

    //Batch uploading/deleting
    $app->route('/api/public/batchUpload/{moduleName}/',App\Handler\BatchUploadPageHandler::class);

    //Accessing the table

    $app->get('/api/public/{desiredTable:event_table|modules_table|users_table|accessed_table}/{desiredValue:\d+}', App\Handler\DisplayTablePageHandler::class);
    $app->get('/api/public/{desiredTable:event_table|modules_table|users_table|accessed_table}/[{desiredColumn:Event_ID|Date|Module|User|Accessed|Type|Action}={desiredValue}]', App\Handler\DisplayTablePageHandler::class);

    $app->post('/api/public/{desiredTable:event_table|modules_table|users_table|accessed_table}/date={date}&module={module}&user={user}&accessed={accessed}&type={type}&action={action}', App\Handler\DisplayTablePageHandler::class);

    $app->patch('/api/public/{desiredTable:event_table|modules_table|users_table|accessed_table}/{Event_ID:\d+}&{desiredColumn:Event_ID|Date|Module|User|Accessed|Type|Action}={desiredValue}',App\Handler\DisplayTablePageHandler::class);

    $app->put('/api/public/{desiredTable:event_table|modules_table|users_table|accessed_table}/{Event_ID:\d+}&{desiredColumn0}={desiredValue0}&{desiredColumn1}={desiredValue1}&{desiredColumn2}={desiredValue2}&{desiredColumn3}={desiredValue3}&{desiredColumn4}={desiredValue4}&{desiredColumn5}={desiredValue5}',App\Handler\DisplayTablePageHandler::class);
    $app->delete('/api/public/{desiredTable:event_table|modules_table|users_table|accessed_table}/{desiredValue:\d+}',App\Handler\DisplayTablePageHandler::class);

};