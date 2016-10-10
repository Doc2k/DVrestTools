<?hh //strict
<script src="{{ plugin_path('DVrestTools') }}/js/jquery-3.1.1.min.js"></script>
<script>
  function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
      vars[key] = value;
    });
    return vars;
  }

</script>

    namespace DVrestTools\Controllers;
    use Plenty\Plugin\Controller;
    use Plenty\Plugin\Templates\Twig;

    class ContentController extends Controller
    {
      public function getToken(Twig $twig):string
      {
          <script>
            var authtoken=false;
            var autorisation=false;
            var myCallback= getUrlVars()["callback"];
            function getToken(){

              jQuery.ajax({
                  url:'/rest/login?username='+getUrlVars()["user"]+'&password='+getUrlVars()["password"],
                  type:"POST",
                  async: false,
                  cache: false,
                  dataType:"json",
                  headers:{"Access-Control-Allow-Origin": "*"}
              }).done(function(response){
                  console.log(response);
                  authtoken=response.accessToken;
                  autorisation="Bearer "+authtoken;
                  console.log('Token: '+authtoken);
                  if(myCallback){
                    var dieResponse=myCallback+'('+JSON.stringify(response)+')';
                    autorisation=myCallback+'('+JSON.stringify(response)+')';
                  }else{
                    var dieResponse=JSON.stringify(response);
                  }

                  document.write(dieResponse);
              }).fail(function(response){
                   console.log(response);
                   return response;
              });
            }
            getToken();
            </script>
          return $twig->render('DVrestTools::content.getToken', array('hausraus'->''+<script>document.write(autorisation)</script>+''));
      }
      public function getStock(Twig $twig):string
      {

          return $twig->render('DVrestTools::content.getStock');
      }
    }
