<?hh //strict
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;


    class ContentController extends Controller{
      private Request $request;
      /**
      * @param Request $request
      */

      public function __construct(Request $request){
        $this->request = $request;

      }

      public function getToken(Twig $twig):string{
        return $twig->render('DVrestTools::content.getToken', array('user' => $this->request->get('user'), 'passw' => $this->request->get('password'), 'callb' => $this->request->get('callback')));
      }

      public function getStock(Twig $twig, ItemDataLayerRepositoryContract $layerModel):string{
        $augabespalten =['itemDescription' => ['name1'], 'variationStock' => ['stockPhysical', 'stockNet']];
        $itemFilter = ['itemBase.id' => $this->request->get('id')];
        $itemParams = ['language' => 'de'];
        //$Ergebnis = $layerModel->search($augabespalten, $itemFilter, $itemParams);
        return $twig->render('DVrestTools::content.getStock', array('callb' => $this->request->get('callback')));
      }

    }
