<?

require('lib/nusoap.php');
//require_once('lib/nusoap.php');

include "Sqls.php";
include "CtrUtils.php";
include('adodb/adodb.inc.php');


	$obServer = new soap_server();
    $obServer->register('verificaAmbiente');

//----------------------------------------------------------------------------------
    /*
    Executa a busca resumida da licen�a.
    Retorna um XML contendo os dados resumidos das licen�as e suas respectivas equival�ncias.
    */
	function verificaAmbiente() {

      $retorno = 'Monitor v.1.0 - Ag�ncia -> '. CtrUtils::getSiglaEstado(). ' ## ##';

     //----------------------------------------------------------------------------------
     //Algumas configura��es do sistema
      $retorno .= '--------------------------------------------------------------------------------- ##';
      $retorno .= 'Algumas vari�veis ##';
      $retorno .= '--------------------------------------------------------------------------------- ##';

      $retorno .= 'Configura��o da URL do servi�o para publica��o do WSDL = '. CtrUtils::getPathServico(). '##';
  	  $retorno .= 'Tipo da base de dados sendo utilizada = '. CtrUtils::getTipoBaseDados(). '##';
      $retorno .= 'URL para downloads dos arquivos XML que cont�m o resultado da busca simples = '.CtrUtils::getUrlArquivosBuscaSimples(). '##';
      $retorno .= 'A configura��o do Driver para acesso a base de dados � = '. Sqls::getConfigBD(). '## ##';
      //----------------------------------------------------------------------------------


      //----------------------------------------------------------------------------------
      //Veririca se a pasta temp existe e se tem permiss�o de escrita
      $retorno .= '--------------------------------------------------------------------------------- ##';
      $retorno .= 'Dados sobre o diret�rio temp onde s�o salvos os resultados da busca simples ##';
      $retorno .= '--------------------------------------------------------------------------------- ##';

      $dir = CtrUtils::getPastaArquivosXML();
      if (is_dir($dir)) {
        $retorno .= 'A pasta temp existe ##';

        $nomeArquivo = "arquivoTeste.txt";
        $fin = fopen(CtrUtils::getPastaArquivosXML(). $nomeArquivo, "w+");
        $conteudoArquivo = "O sistema monitor conseguiu escrever esta frase dentro deste arquivo com sucesso !!!";
        fputs($fin, $conteudoArquivo, strlen($conteudoArquivo));
 	    fclose($fin);

        if (is_file(CtrUtils::getPastaArquivosXML(). $nomeArquivo)) {
          $retorno .= 'A pasta temp est� com permiss�o de escrita. ## ##';
          unlink(CtrUtils::getPastaArquivosXML(). $nomeArquivo);
        } else {
          $retorno .= 'A pasta temp N�O est� com permiss�o de escrita. ## ##';
        }
      } else {
        $retorno .= 'A pasta temp N�O existe ##';
      }
     //----------------------------------------------------------------------------------



     //----------------------------------------------------------------------------------
     //Verifica conectividade com a base de dados
      $retorno .= '--------------------------------------------------------------------------------- ##';
      $retorno .= 'Dados sobre o diret�rio base de dados ##';
      $retorno .= '--------------------------------------------------------------------------------- ##';

      $r = CtrUtils::verificaStatusBD();
      if ($r == '-1')
        $retorno .= 'N�O foi poss�vel conectar-se a base de dados  !!! ##';
      else
        $retorno .= 'O sistema conectou na base de dados com sucesso !!! ## ##';
     //----------------------------------------------------------------------------------


     //----------------------------------------------------------------------------------
     //Executa uma consulta da base de dados e retorna o n�mero de licen�as
      $campoDataEmissao = 0;
      $campoDataProtocolo = 0;
      $campoDataVencimento = 0;
      $campoNumeroLicenca = 0;
      $campoInstituicaoOrigem = 0;
      $campoUFEmpreendimento = 0;
      $numTotalReg = 0;

      //$db =& ADONewConnection(CtrUtils::getTipoBaseDados());
	  //$db->Connect(Sqls::getConfigBD());
      $db =& ADONewConnection(Sqls::getConfigBD());
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      $con = $db;
      //$rs = $con->Execute("select * from en_licencas");
	  $rs = $con->Execute("select * from ws_pnla.\"EN_LICENCAS\"");
      if ($rs) {
        while (!$rs->EOF) {

          if ($rs->fields["NUMERO_LICENCA"] == "") {
            $campoNumeroLicenca++;
          }
          if ($rs->fields["UF_EMPREENDIMENTO_PRINC"] == "") {
            $campoUFEmpreendimento++;
          }
          if ($rs->fields["INSTITUICAO_DE_ORIGEM"] == "") {
            $campoInstituicaoOrigem++;
          }

          $numTotalReg++;
          
          $rs->MoveNext();
        }
        $rs->Close();
      }
      $con->Close();

      $retorno .= "Os campos listados a seguir N�O podem ter registros com valor em branco: ## ##";
      
      $retorno .= "       -> H� ". $campoNumeroLicenca. " licen�as com o campo NUMERO_LICENCA em branco. ##";
      $retorno .= "       -> H� ". $campoUFEmpreendimento. " licen�as com o campo UF_EMPREENDIMENTO_PRINC em branco. ##";
      $retorno .= "       -> H� ". $campoInstituicaoOrigem. " licen�as com o campo INSTITUICAO_DE_ORIGEM em branco. ## ##";

      $retorno .= "N�mero total de licen�as ". $numTotalReg;


      return $retorno;
      }
     //----------------------------------------------------------------------------------
      

//----------------------------------------------------------------------------------
  $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
  $obServer->service($HTTP_RAW_POST_DATA);
?>




