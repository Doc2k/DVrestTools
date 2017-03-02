<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
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
        public function getVisibilities(Twig $twig, ItemDataLayerRepositoryContract $repo, VariationRepositoryContract $VarRepo, ContactAuthenticationRepositoryContract $authRepo){

          /* Fuer spaeteren vergleich (In diesem Shop sichtbar) */
          /* ---------------------------------------------------- */
            $plentyId = '18507';
          /* ---------------------------------------------------- */

          /* $ergebnisse wird spaeter an Twig uebergeben */
          /* ---------------------------------------------------- */
            $ergebnisse = array();
          /* ---------------------------------------------------- */

          /* Erster Call (DataLayerRepo) */
          /* ============================================================================ */

            /* Spalten die beim ersten Call abgefragt werden */
            /* ---------------------------------------------------- */
              $augabespalten =[
                'itemBase' => ['id'],
                'itemDescription' => ['name1'],
                'variationBase' => ['id', 'variationName'],
                'variationStock' => ['stockNet']
              ];
            /* ---------------------------------------------------- */

            /* Filter auf ItemId fuer ersten Call (muss spaeter weg) */
            /* ---------------------------------------------------- */
              $itemFilter = ['itemBase.hasId' => ['itemId' => [$this->request->get('id')]]];
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
              foreach($Ergebnis as $item){

                /* Werte aus erstem Call in Vars uebernehmen */
                /* ---------------------------------------------------- */
                  $ergebnisse[] = $item;
                  $itemID= $item['itemBase']['id'];
                  $varID= $item['variationBase']['id'];
                  $varName= $item['variationBase']['variationName'];
                  $itemName= $item['itemDescription']['name1'];
                  $itemNettoStock= $item['variationStock']['stockNet'];
                /* ---------------------------------------------------- */

                /* Zweiten Call mit den sichtbaren Clients durchfuehren ($with) */
                /* ---------------------------------------------------- */
                  $with['variationClients'] = true;
                  $VariationAbfrage = $VarRepo->show($varID, $with, "de");
                /* ---------------------------------------------------- */

                /* Ergebnis von zweitem Call loggen (Datentausch -> Log) */
                /* ---------------------------------------------------- */
                  $this
                    ->getLogger("ContentController_show")
                    ->setReferenceType('VariationRepositoryContract')
                    ->setReferenceValue($varID)
                    ->info('DVrestTools::log.successMessage', $VariationAbfrage);
                /* ---------------------------------------------------- */

                /* Werte aus 2tem Call in Vars uebernehmen*/
                /* ============================================================================ */
                    $Varergebnisse = array();
                    $Varergebnisse[] = $VariationAbfrage;
                    $autoSichtbar= (string)$Varergebnisse[0]['isVisibleIfNetStockIsPositive'];
                    $autoUnsichtbar= (string)$Varergebnisse[0]['isInvisibleIfNetStockIsNotPositive'];
                    $autoGruen= (string)$Varergebnisse[0]['isAvailableIfNetStockIsPositive'];
                    $autoRot= (string)$Varergebnisse[0]['isUnavailableIfNetStockIsNotPositive'];
                    $varActive = (string)$Varergebnisse[0]['isActive'];

                    /* Alle uebermittelten Clients auf gesuchte PlentyID pruefen */
                    /* ---------------------------------------------------- */
                      $istaktuellSichtbar='nein';
                      foreach($Varergebnisse[0]['variationClients'] as $client){
                        if((string)$client['plentyId']==$plentyId){
                          $istaktuellSichtbar='ja';
                        }
                      }
                    /* ---------------------------------------------------- */

                /* ============================================================================ */
                /* ENDE -> Werte aus 2tem Call in Vars uebernehmen*/

                /* Interpaetation der geammelten Werte */
                /* ============================================================================ */

                /* ============================================================================ */
                /* ENDE -> Interpaetation der geammelten Werte */


                echo '<div>ZweiterCall<br />ItemID:'.$itemID.' | VarID:'.$varID.' | ItemName:'.$varID.' | VarName:'.$varName.' | Aktiv:'.$varActive.' | Beschränkung:'.$beschraenkung.' | AutoSichtbar:'.$autoSichtbar.' | AutoUnsichtbar:'.$autoUnsichtbar.'| Aktuell sichtbar:'.$istaktuellSichtbar.'</div>';
              }
            /* ============================================================================ */
            /* ENDE -> Ergebnis von erstem Call in Schleife durchlaufen */

          /* ============================================================================ */
          /* ENDE -> Erster Call (DataLayerRepo) */

        }
      /* ############################################################################################################ */
      /* ENDE -> Get Visibilities */

}
