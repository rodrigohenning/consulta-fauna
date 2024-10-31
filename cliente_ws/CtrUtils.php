<?
class CtrUtils {


//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo retorna a sigla do estado ou a silga da ag�ncia
    */
    function getSiglaEstado() {
      return "AC";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo retorna a URL do servi�o no estado.
    OBS: N�o utilize o termo "localhost" para identifica��o do IP, tampouco o IP 127.0.0.1. Utilize o IP real do servidor.
    */
    function getPathServico() {
      return "http://179.252.114.92:8074/seiam/cliente_ws/";
      //returno "http://200.103.16.11:82/ClienteWS_AC/";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta a URL do servi�o no estado.
    */
    function getUrlServico() {
      return CtrUtils::getPathServico(). "MMA_Licencas.php";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta a URL do diret�rio que cont�m os arquivos com o resultado das buscas simples
    */
    function getUrlArquivosBuscaSimples() {
      return CtrUtils::getPathServico(). CtrUtils::getPastaArquivosXML();
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo retorna o nome da pasta que ir� armazenar os arquivos XML que cont�m o resultado da busca simplificada
    */
    function getPastaArquivosXML() {
      return "temp/";
    }


//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    M�todo que retorna o tipo da base de dados que est� sendo utilizada.
    */
    function getTipoBaseDados() {
      return "postgresql8";
    }


//------------------------------------------------------------------------------------------------------------------------------------------
   /*
   Este m�todo faz a substitui��o de todos os caracteres especiais por caracteres aceit�veis pelo html e xml.
   */
   function escape($str) {
     return htmlspecialchars($str);
   }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo faz a verifica��o se a base de dados est� on-line e acess�vel.
    Retorna uma string com o valor "-1" caso n�o consiga acessar a base de dados.
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
    Este m�todo deleta os antigos arquivos que armazenam o resultado da busca simples na pasta temp
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

