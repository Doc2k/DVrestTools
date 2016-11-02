<?hh //strict
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
    use Plenty\Plugin\Http\Request;
    use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
    use Plenty\Modules\Item\DataLayer\Models;

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

      public function getStock(Twig $twig, ItemDataLayerRepositoryContract $bestaende):string{
        $augabespalten =['itemDescription' => ['name1']];
        $itemFilter = ['itemBase'->'id' => '19001'];
        $itemParams = ['language' => 'de'];
        $Ergebnis = $bestaende->search($augabespalten, $itemFilter, $itemParams);
        return $twig->render('DVrestTools::content.getStock', array('callb' => $this->request->get('callback'), 'erg' => $Ergebnis));
      }

    }
