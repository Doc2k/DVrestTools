<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;

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
        public function getStock(Twig $twig, ItemDataLayerRepositoryContract $repo, VariationStockRepositoryContract $repo2):string{
          header('content-type: application/json; charset=utf-8');
          header("access-control-allow-origin: *");

          $augabespalten =[
            'itemDescription' => ['name1'],
            'variationBase' => ['id'],
            'variationStock' => ['stockNet', 'stockPhysical', 'warehouseId']
          ];
          $itemFilter = ['itemBase.hasId' => ['itemId' => [$this->request->get('id')]]];
          $itemParams = ['language' => 'de', 'type' => 'virtual'];
          $Ergebnis = $repo->search($augabespalten, $itemFilter, $itemParams);
          $ergebnisse = array();
          $stockColumns= ['itemId', 'stockNet', 'stockPhysical', 'warehouseId'];
          echo $Ergebnis->variationBase->id;
          foreach($Ergebnis as $item){
            echo $item->variationBase->id;
            $Stockergebnis= $repo2->listStockByWarehouse($item->variationBase->id, $stockColumns);
            //foreach($Stockergebnis as $bestand){
              //$item+=['Stock_netto' => $bestand->stockNet];
              //$item+=['Stock_Phys' => $bestand->stockPhysical];

            //}
            $ergebnisse[] = $item;
          }

          $myData= array(
            'inhalte' => $ergebnisse,
            'callb' => $this->request->get('callback')
          );

          return $twig->render('DVrestTools::content.getStock', $myData);
        }
      // ----------------------------------------------------
}
