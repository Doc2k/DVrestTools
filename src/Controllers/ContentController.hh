<?hh //strict

    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;

    class ContentController extends Controller
    {
      public function getToken(Twig $twig):string
      {
        
          return $twig->render('DVrestTools::content.getToken');
      }
      public function getStock(Twig $twig):string
      {

          return $twig->render('DVrestTools::content.getStock');
      }
    }
