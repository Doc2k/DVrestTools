<?php

    namespace DVrestTools\Providers;


    use Plenty\Plugin\ServiceProvider;

    class DVrestToolsServiceProvider extends ServiceProvider
    {
      /* Register the service provider. */
      /* ---------------------------------------------- */
          public function register(){
            $this->getApplication()->register(DVrestToolsRouteServiceProvider::class);
          }
      /* ---------------------------------------------- */
    }
