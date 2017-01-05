<?php
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;


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

          return $twig->render('DVrestTools::content.getStock', $myData);
        }
      // ----------------------------------------------------


      // Set Stock
      // ----------------------------------------------------
        public function setStock(VariationStockRepositoryContract $repo1){
          $correctColumns=['warehouseId'=>$this->request->get('warehouse'), 'quantity'=>$this->request->get('quant'), 'storageLocationId'=>0];

          $repo1->correctStock(1017, $correctColumns);
          return ($this->request->get('callback')."({'success': 'true', 'newStock' : ".$this->request->get('quant')."})");
      // ----------------------------------------------------
    }
}
