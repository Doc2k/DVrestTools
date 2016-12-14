<?php

    namespace DVrestTools\Providers;


    use Plenty\Plugin\RouteServiceProvider;
    use Plenty\Plugin\Routing\Router;

    class DVrestToolsRouteServiceProvider extends RouteServiceProvider
    {
        public function map(Router $router)
        {
          $router->get('auth','DVrestTools\Controllers\ContentController@getToken');
          $router->get('getstock','DVrestTools\Controllers\ContentController@getStock');
          $router->get('getstock2','DVrestTools\Controllers\ContentController@getStock2');
        }
    }
