<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
    use Plenty\Modules\Item\VariationSalesPrice\Contracts\VariationSalesPriceRepositoryContract;
    // use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceNameRepositoryContract;
    use Plenty\Plugin\Log\Loggable;



    class ContentController extends Controller{
      use Loggable;

      /* Superglobale GET einbinden */
      /* ---------------------------------------------------- */
        public $request;
        /**
        * @param Request $request
        */

        public function __construct(Request $request){
          $this->request = $request;
        }
      /* ---------------------------------------------------- */

      // Get Token vorerst unbenutzt
      /* ---------------------------------------------------- */
        public function getToken(Twig $twig):string{
          return $twig->render('DVrestTools::content.getToken', array('user' => $this->request->get('user'), 'passw' => $this->request->get('password'), 'callb' => $this->request->get('callback')));
        }
      /* ---------------------------------------------------- */



      // Get Stock
      /* ---------------------------------------------------- */
        public function getStock(Twig $twig, ItemDataLayerRepositoryContract $repo):string{
          header('content-type: application/json; charset=utf-8');
          header("access-control-allow-origin: *");

          $augabespalten =[
            'itemDescription' => ['name1'],
            'variationBase' => ['id'],
            'variationStock' => ['stockNet', 'stockPhysical', 'warehouseId']
          ];
          $itemFilter = ['itemBase.hasId' => ['itemId' => [$this->request->get('id')]]];
          $itemParams = ['language' => 'de', 'type' => 'warehouseId', 'warehouseId' => $this->request->get('warehouse')];
          $Ergebnis = $repo->search($augabespalten, $itemFilter, $itemParams);
          $ergebnisse = array();
          foreach($Ergebnis as $item){
            $ergebnisse[] = $item;
          }

          $myData= array(
            'inhalte' => $ergebnisse,
            'callb' => $this->request->get('callback')
          );

          return $twig->render('DVrestTools::content.getStockB', $myData);
        }
      /* ---------------------------------------------------- */


      // Set Stock
      /* ---------------------------------------------------- */
        public function setStock(Twig $twig, VariationStockRepositoryContract $repo1){
          header('content-type: application/json; charset=utf-8');
          header("access-control-allow-origin: *");
          $correctColumns=['warehouseId'=>$this->request->get('warehouse'),'variationId'=>$this->request->get('variation_id'), 'quantity'=>$this->request->get('quant'), 'storageLocationId'=>0];
          $antwort=array();
          $repo1->correctStock($this->request->get('id'), $correctColumns);

          $myData= array(
            'menge' => $this->request->get('quant'),
            'callb' => $this->request->get('callback')
          );
          return $twig->render('DVrestTools::content.setStock', $myData);
        }
      /* ---------------------------------------------------- */

      /* Get Visibilities */
      /* ############################################################################################################ */
        public function getVisibilities(Twig $twig, ItemDataLayerRepositoryContract $repo, VariationRepositoryContract $VarRepo){

          /* Fuer spaeteren vergleich (In diesem Shop sichtbar) */
          /* ---------------------------------------------------- */
            $plentyId = $this->request->get('pID');
          /* ---------------------------------------------------- */

          /* $ergebnisse wird spaeter an Twig uebergeben */
          /* ---------------------------------------------------- */
            $ergebnisse = [];
          /* ---------------------------------------------------- */

          /* Erster Call (DataLayerRepo) */
          /* ============================================================================ */

            /* Spalten die beim ersten Call abgefragt werden */
            /* ---------------------------------------------------- */
              $augabespalten =[
                'itemBase' => ['id', 'free7'],
                'itemDescription' => ['name1'],
                'variationBase' => ['id', 'variationName', 'active'],
                'variationStock' => ['stockNet']
              ];
            /* ---------------------------------------------------- */

            /* Filter auf ItemId fuer ersten Call (muss spaeter weg) */
            /* ---------------------------------------------------- */
              // $itemFilter = [];
              $itemFilter = ['itemBase.hasId' => ['itemId' => '61010']];
              // $itemFilter = ['variationBase.isActive' => 'true'];
            /* ---------------------------------------------------- */

            /* Parameter fuer ersten Call (Einschraenkung auf Lager) */
            /* ---------------------------------------------------- */
              $itemParams = ['language' => 'de', 'type' => 'warehouseId', 'warehouseId' => $this->request->get('warehouse')];
            /* ---------------------------------------------------- */

            /* Ersten Call durchfueheren (Search auf DataLayer) */
            /* ---------------------------------------------------- */
              $Ergebnis = $repo->search($augabespalten, $itemFilter, $itemParams);
            /* ---------------------------------------------------- */

            /* Ergebnis von erstem Call in Schleife durchlaufen */
            /* ============================================================================ */

              $itemCount=0;

              foreach($Ergebnis as $item){

                /* Werte aus erstem Call in $ergebnisse einfuegen */
                /* ---------------------------------------------------- */
                  $ergebnisse[$itemCount]['itemBase']['id'] = $item['itemBase']['id'];
                  $ergebnisse[$itemCount]['itemBase']['free7'] = $item['itemBase']['free7'];
                  $ergebnisse[$itemCount]['itemDescription']['name1'] = $item['itemDescription']['name1'];
                  $ergebnisse[$itemCount]['variationBase']['id'] = $item['variationBase']['id'];
                  $ergebnisse[$itemCount]['variationBase']['variationName'] = $item['variationBase']['variationName'];
                  $ergebnisse[$itemCount]['variationBase']['isActive'] = $item['variationBase']['active'];
                  $ergebnisse[$itemCount]['variationStock']['stockNet'] = $item['variationStock']['stockNet'];
                /* ---------------------------------------------------- */

                if(($item['itemBase']['free7']=="" || $item['itemBase']['free7']=="0") &&  ($item['variationBase']['active']===true)){
                  /* ---------------------------------------------------- */
                  /* ---------------------------------------------------- */
                    $itemID= $item['itemBase']['id'];
                    $varID= $item['variationBase']['id'];
                  /* ---------------------------------------------------- */

                  /* Zweiten Call mit den sichtbaren Clients durchfuehren ($with) */
                  /* ---------------------------------------------------- */
                    $with['variationClients'] = true;
                    $VariationAbfrage = $VarRepo->show($varID, $with, "de");
                  /* ---------------------------------------------------- */


                  /* Werte aus 2tem Call in $ergebnisse uebernehmen */
                  /* ============================================================================ */
                      $Varergebnisse = array();
                      $Varergebnisse[] = $VariationAbfrage;

                      $ergebnisse[$itemCount]['variationBase']['isVisibleIfNetStockIsPositive'] = $Varergebnisse[0]['isVisibleIfNetStockIsPositive'];
                      $ergebnisse[$itemCount]['variationBase']['isInvisibleIfNetStockIsNotPositive'] = $Varergebnisse[0]['isInvisibleIfNetStockIsNotPositive'];
                      $ergebnisse[$itemCount]['variationBase']['isAvailableIfNetStockIsPositive'] = $Varergebnisse[0]['isAvailableIfNetStockIsPositive'];
                      $ergebnisse[$itemCount]['variationBase']['isUnavailableIfNetStockIsNotPositive'] = $Varergebnisse[0]['isUnavailableIfNetStockIsNotPositive'];
                      $ergebnisse[$itemCount]['variationBase']['stockLimitation'] = $Varergebnisse[0]['stockLimitation'];
                      $ergebnisse[$itemCount]['variationBase']['mainWarehouseId'] = $Varergebnisse[0]['mainWarehouseId'];
                      $ergebnisse[$itemCount]['variationBase']['variationName'] = $Varergebnisse[0]['name'];
                      $ergebnisse[$itemCount]['variationBase']['variationAvail'] = $Varergebnisse[0]['availability'];
                      $ergebnisse[$itemCount]['variationBase']['autoVisible'] = $Varergebnisse[0]['automaticClientVisibility'];

                      /* Alle uebermittelten Clients auf gesuchte PlentyID pruefen */
                      /* ---------------------------------------------------- */
                        $istaktuellSichtbar='nein';
                        foreach($Varergebnisse[0]['variationClients'] as $client){
                          if((string)$client['plentyId']==$plentyId){
                              $istaktuellSichtbar='ja';
                          }
                        }

                        $ergebnisse[$itemCount]['variationBase']['isVisibleInClient'] = $istaktuellSichtbar;
                      /* ---------------------------------------------------- */
                  /* ============================================================================ */
                  /* ENDE -> Werte aus 2tem Call in Vars uebernehmen*/


                }
                $itemCount++;




              }
            /* ============================================================================ */
            /* ENDE -> Ergebnis von erstem Call in Schleife durchlaufen */

            /* $ergebnisse loggen (Datentausch -> Log) */
            /* ---------------------------------------------------- */
              $this
                ->getLogger("ContentController_show")
                ->setReferenceType('VariationRepositoryContract')
                ->setReferenceValue($varID.'_'.$itemCount)
                ->info('DVrestTools::log.successMessage', $ergebnisse);
            /* ---------------------------------------------------- */

          /* ============================================================================ */
          /* ENDE -> Erster Call (DataLayerRepo) */

          $myData= ['inhalte' => $ergebnisse];
          return $twig->render('DVrestTools::content.getVisibilities', $myData);
        }
      /* ############################################################################################################ */
      /* ENDE -> Get Visibilities */




      /* Get SalesPrices */
      /* ############################################################################################################ */
        public function getPrices(Twig $twig, VariationSalesPriceRepositoryContract $pricerepo):string{
          header('content-type: application/json; charset=utf-8');
          header("access-control-allow-origin: *");

          $augabespalten =[
            'itemDescription' => ['name1'],
            'variationBase' => ['id'],
            'variationStock' => ['stockNet', 'stockPhysical', 'warehouseId']
          ];
          $itemFilter = ['itemBase.hasId' => ['itemId' => [$this->request->get('id')]]];
          $itemParams = ['language' => 'de', 'type' => 'warehouseId', 'warehouseId' => $this->request->get('warehouse')];
          $Ergebnis = $pricerepo->search($augabespalten, $itemFilter, $itemParams);
          $ergebnisse = array();
          foreach($Ergebnis as $item){
            $ergebnisse[] = $item;
          }

          $myData= array(
            'inhalte' => $ergebnisse,
            'callb' => $this->request->get('callback')
          );

          return $twig->render('DVrestTools::content.getStockB', $myData);
        }
      /* ############################################################################################################ */
      /* ENDE -> Get SalesPrices */
}
