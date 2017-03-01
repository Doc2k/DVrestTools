<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
    use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;



    class ContentController extends Controller{
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
            echo($item['variationBase']['id']);
            $ergebnisse[] = $item;
            // echo($ergebnisse[0]['itemBase']['id']);
            $itemID= $item['itemBase']['id'];
            $varID= $item['variationBase']['id'];

            $VariationAbfrage = $VarRepo->show($varID, ['isActive', 'stockLimitation', 'isVisibleIfNetStockIsPositive', 'isInvisibleIfNetStockIsNotPositive', 'isAvailableIfNetStockIsPositive', 'isUnavailableIfNetStockIsNotPositive', 'variationClients'], 'de');
            foreach($VariationAbfrage as $varItem){
              $beschraenkung= $varItem['stockLimitation'];
              $autoSichtbar= $varItem['isAvailableIfNetStockIsPositive'];
              $autoUnsichtbar= $varItem['isAvailableIfNetStockIsNotPositive'];
              $autoGruen= $varItem['isAvailableIfNetStockIsPositive'];
              $autoRot= $varItem['isUnavailableIfNetStockIsNotPositive'];
              $varActive = $varItem['isActive'];
              echo '<div>ItemID:'.$itemID.' | VarID:'.$varID.' | Aktiv:'.$varActive.' | Beschränkung:'.$beschraenkung.' | AutoSichtbar:'.$autoSichtbar.' | ';
              echo '<div>'.$zaehler.'</div>';
              $clientzaehler=0;
              foreach($varItem['variationClients'] as $client){
                echo 'Client:'.$client[$clientzaehler];
                $clientzaehler++;
              }
              echo '<div>';
            }

            $zaehler++;
          }


        }
      // ----------------------------------------------------

}
