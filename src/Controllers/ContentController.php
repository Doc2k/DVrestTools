<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;


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
          $stockColumns= array('stockNet');
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
        public function getVisibilities(Twig $twig, ItemDataLayerRepositoryContract $DataLayer, VariationRepositoryContract $VariationRepository):string{
          header('content-type: application/json; charset=utf-8');
          header("access-control-allow-origin: *");

          $augabespalten =[
            'itemBase' => ['id'],
            'itemDescription' => ['name1'],
            'variationBase' => ['id', 'itemId', 'variationName', 'limitOrderByStockSelect', 'autoStockVisible', 'autoStockInvisible', 'active', 'availability', 'mainWarehouse'],
            'variationStock' => ['stockNet', 'stockPhysical', 'warehouseId']
          ];
          // $itemFilter = ['isVisibleForClient'=> ['clientId' => [0]]];
          $itemFilter = [];
          $itemParams = ['language' => 'de', 'type' => 'virtual'];

          $ItemSuche = $DataLayer->search($augabespalten, $itemFilter, $itemParams);
          $ergebnisse = array();
          foreach($ItemSuche as $item){
            echo '<div>';
            $itemID= $item.itemBase.id;
            $varID= $item.variationBase.id;

            $itemAvail= $item.variationBase.availability;

            $VariationAbfrage = $VariationRepository.findById($varID);

            $beschraenkung= $VariationAbfrage.stockLimitation;
            $autoSichtbar= $VariationAbfrage.isAvailableIfNetStockIsPositive;
            $autoUnsichtbar= $VariationAbfrage.isAvailableIfNetStockIsNotPositive;
            $autoGruen= $VariationAbfrage.isAvailableIfNetStockIsPositive;
            $autoRot= $VariationAbfrage.isUnavailableIfNetStockIsNotPositive;
            $varActive = $VariationAbfrage.isActive;
            echo 'ItemID:'.$itemID.' | VarID:'.$varID.' | Aktiv:'.$varActive.' | Beschränkung:'.$beschraenkung.' | AutoSichtbar:'.$autoSichtbar.' | ';
            foreach($VariationAbfrage.variationClients as $client){
              echo 'Client:'.$client.ClientId;
            }
            echo '<div>';



          }

        }
      // ----------------------------------------------------

}
