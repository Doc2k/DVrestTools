<?hh //strict
    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;
      use Plenty\Plugin\Http\Request;


    class ContentController extends Controller
    {
      public function getToken(Twig $twig):string
      {
          return $twig->render('DVrestTools::content.getToken', array('user' => '$_GET['user']'));
      }
      public function getStock(Twig $twig):string
      {

          return $twig->render('DVrestTools::content.getStock');
      }
    }
