<?php
use Cake\Routing\Router;

Router::plugin('SpongeCake', function ($routes) {

    Router::scope('/', function ($routes) {

        $routes->connect('/pages/:action/*', ['plugin' => 'SpongeCake', 'controller' => 'Contents']);
        $routes->connect('/*', ['plugin' => 'SpongeCake', 'controller' => 'Contents', 'action' => 'display'], ['routeClass' => 'SpongeCake.ContentsRoute']);
        $routes->fallbacks('InflectedRoute');
    });

});
