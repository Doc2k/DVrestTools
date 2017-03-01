<?php

    namespace DVrestTools\Providers;
    use Plenty\Plugin\ServiceProvider;
    use Plenty\Log\Services\ReferenceContainer;
    use Plenty\Log\Exceptions\ReferenceTypeException;

    class DVrestToolsServiceProvider extends ServiceProvider
    {
      /* Register the service provider. */
      /* ---------------------------------------------- */
          public function register(){
            $this->getApplication()->register(DVrestToolsRouteServiceProvider::class);
          }
      /* ---------------------------------------------- */

      public function boot(ReferenceContainer $referenceContainer)
        {
            // Register reference types for logs.
            try
            {
                $referenceContainer->add([ 'DVRestTools' => 'DvRestTools' ]);
            }
            catch(ReferenceTypeException $ex)
            {
            }
        }
    }
