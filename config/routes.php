<?php
/**
 * Routing configurations - http://www.slimframework.com/docs/v3/objects/router.html
 * TODO: properly abstract this into a configuration array that gets used to generate the routes inside of index.php
 */

$app->get('/healthz', \App\Controllers\BaseController::class . ':healthz')
    ->setName('healthz');

$app->post('/', \App\Controllers\BaseController::class . ':graphql')
    ->setName('graphql');
