<?hh //strict
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Models\VariationStock;
    class ContentController extends Controller{
      // Superglobale GET einbinden
      // ----------------------------------------------------
        private Request $request;
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

      // Get Stock also irgendwann mal
      // ----------------------------------------------------
        public function getStock(Twig $twig, ItemDataLayerRepositoryContract $repo, VariationStock $stockrepo):string{
          $augabespalten =[
            'itemDescription' => ['name1'],
            'variationBase' => ['id'],
          ];
          $itemFilter = ['itemBase.hasId' => ['itemId' => [$this->request->get('id')]]];
          $itemParams = ['language' => 'de'];
          $Ergebnis = $repo->search($augabespalten, $itemFilter, $itemParams);
          $ergebnisse = array();


          foreach($Ergebnis as $item){
            $augabespaltenStock =['stockNet'];
            $nettobestand = $stockrepo->search($augabespaltenStock, $itemFilter, $itemParams)

            $ergebnisse[] = $item->$nettobestand;
          }

          $myData= array(
            'inhalte' => $ergebnisse,
            'callb' => $this->request->get('callback')
          );

          return $twig->render('DVrestTools::content.getStock', $myData);
        }
      // ----------------------------------------------------
    }
