<?
class CtrUtils {


//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método retorna a sigla do estado ou a silga da agência
    */
    function getSiglaEstado() {
      return "AC";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método retorna a URL do serviço no estado.
    OBS: Não utilize o termo "localhost" para identificação do IP, tampouco o IP 127.0.0.1. Utilize o IP real do servidor.
    */
    function getPathServico() {
      return "http://179.252.114.92:8074/seiam/cliente_ws/";
      //returno "http://200.103.16.11:82/ClienteWS_AC/";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método monta a URL do serviço no estado.
    */
    function getUrlServico() {
      return CtrUtils::getPathServico(). "MMA_Licencas.php";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método monta a URL do diretório que contém os arquivos com o resultado das buscas simples
    */
    function getUrlArquivosBuscaSimples() {
      return CtrUtils::getPathServico(). CtrUtils::getPastaArquivosXML();
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método retorna o nome da pasta que irá armazenar os arquivos XML que contém o resultado da busca simplificada
    */
    function getPastaArquivosXML() {
      return "temp/";
    }


//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que retorna o tipo da base de dados que está sendo utilizada.
    */
    function getTipoBaseDados() {
      return "postgresql8";
    }


//------------------------------------------------------------------------------------------------------------------------------------------
   /*
   Este método faz a substituição de todos os caracteres especiais por caracteres aceitáveis pelo html e xml.
   */
   function escape($str) {
     return htmlspecialchars($str);
   }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método faz a verificação se a base de dados está on-line e acessível.
    Retorna uma string com o valor "-1" caso não consiga acessar a base de dados.
    */
    function verificaStatusBD() {
   	  //$db =& ADONewConnection(CtrUtils::getTipoBaseDados());
	  //$db->Connect(Sqls::getConfigBD());
   	  $db =& ADONewConnection(Sqls::getConfigBD());
	  //$db->Connect(Sqls::getConfigBD());
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      $rs = $db->Execute(Sqls::getSqlTestarConexao());
      $temp = "-1";
      if ($rs)
       if (!$rs->EOF)
        $temp = "";
      $rs->Close();
      $db->Close();
      return $temp;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este método deleta os antigos arquivos que armazenam o resultado da busca simples na pasta temp
    */
    function deletaArquivosDirTemp() {
      $dir = CtrUtils::getUrlArquivosBuscaSimples();
      if (is_dir($dir)) {
       if ($dh = opendir($dir)) {
         while (($file = readdir($dh)) !== false) {
	       if ($file != "." && $file != "..") {
	         if (time() - filemtime($dir . $file) > 86400) {
		   	   unlink($dir. $file);
		     }
 	       }
         }
         closedir($dh);
       }
      }
    }
}
?>

