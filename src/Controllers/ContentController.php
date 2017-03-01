<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
    use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
    use Plenty\Plugin\Log\Loggable;



    class ContentController extends Controller{
      use Loggable;

      // Superglobale GET einbinden
      // ----------------------------------------------------
        public $request;
        /**
        * @param Request $request
        */

        public function __construct(Request $request){
          $this->request = $request;
        }
      // ----------------------------------------------------

      // Get Token vorerst unbenutzt
      // ----------------------------------------------------
        public function getToken(Twig $twig):string{
          return $twig->render('DVrestTools::content.getToken', array('user' => $this->request->get('user'), 'passw' => $this->request->get('password'), 'callb' => $this->request->get('callback')));
        }
      // ----------------------------------------------------



      // Get Stock
      // ----------------------------------------------------
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
      // ----------------------------------------------------


      // Set Stock
      // ----------------------------------------------------
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
      // ----------------------------------------------------

      // Get Visibilities
      // ----------------------------------------------------
        public function getVisibilities(Twig $twig, ItemDataLayerRepositoryContract $repo, VariationRepositoryContract $VarRepo, ContactAuthenticationRepositoryContract $authRepo){
          //$login= $authRepo->authenticateWithContactId(15, 'DvR3sT4p1Us3r!');

          $augabespalten =[
            'itemBase' => ['id'],
            'itemDescription' => ['name1'],
            'variationBase' => ['id', 'itemId', 'variationName', 'limitOrderByStockSelect', 'autoStockVisible', 'autoStockInvisible', 'active', 'availability', 'mainWarehouse'],
            'variationStock' => ['stockNet', 'stockPhysical', 'warehouseId']
          ];
          $itemFilter = ['itemBase.hasId' => ['itemId' => [$this->request->get('id')]]];
          $itemParams = ['language' => 'de', 'type' => 'warehouseId', 'warehouseId' => $this->request->get('warehouse')];
          $Ergebnis = $repo->search($augabespalten, $itemFilter, $itemParams);
          $ergebnisse = array();
          $zaehler=0;
          foreach($Ergebnis as $item){
            $ergebnisse[] = $item;
            // echo($ergebnisse[0]['itemBase']['id']);
            $itemID= $item['itemBase']['id'];
            $varID= $item['variationBase']['id'];

            $with['variationClients'] = true;
            $lang = "de";
            $VariationAbfrage = $VarRepo->show($varID, $with, $lang);
            $this
              ->getLogger("ContentController_show")
              ->setReferenceType('VariationRepositoryContract')
              ->setReferenceValue($varID)
              ->info('DVrestTools::log.successMessage', $VariationAbfrage);
            $Varergebnisse = array();
            $Varergebnisse[] = $VariationAbfrage;
            $varabfrageZaehler=0;
            $myText = (string)$Varergebnisse[0]['mainWarehouseId'];
            echo('Lala:'$myText);

            $beschraenkung= (string)$VariationAbfrage->stockLimitation;
            $autoSichtbar= (string)$VariationAbfrage->isVisibleIfNetStockIsPositive;
            $autoUnsichtbar= (string)$VariationAbfrage->isInvisibleIfNetStockIsNotPositive;
            $autoGruen= (string)$VariationAbfrage->isAvailableIfNetStockIsPositive;
            $autoRot= (string)$VariationAbfrage->isUnavailableIfNetStockIsNotPositive;
            $varActive = (string)$VariationAbfrage->isActive;
            echo '<div>ItemID:'.$VariationAbfrage->id.' | VarID:'.$varID.' | Aktiv:'.$varActive.' | Beschränkung:'.$beschraenkung.' | AutoSichtbar:'.$autoSichtbar.' | Clients:';
            $varabfrageZaehler++;
            $clientzaehler=0;
            foreach($VariationAbfrage->variationClients as $client){
              echo 'Client:'.$client;
              $clientzaehler++;
            }
            echo '<div>';
            $zaehler++;
          }


        }
      // ----------------------------------------------------

}
