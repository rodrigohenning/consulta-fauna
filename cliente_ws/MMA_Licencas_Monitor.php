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
    Executa a busca resumida da licença.
    Retorna um XML contendo os dados resumidos das licenças e suas respectivas equivalências.
    */
	function verificaAmbiente() {

      $retorno = 'Monitor v.1.0 - Agência -> '. CtrUtils::getSiglaEstado(). ' ## ##';

     //----------------------------------------------------------------------------------
     //Algumas configurações do sistema
      $retorno .= '--------------------------------------------------------------------------------- ##';
      $retorno .= 'Algumas variáveis ##';
      $retorno .= '--------------------------------------------------------------------------------- ##';

      $retorno .= 'Configuração da URL do serviço para publicação do WSDL = '. CtrUtils::getPathServico(). '##';
  	  $retorno .= 'Tipo da base de dados sendo utilizada = '. CtrUtils::getTipoBaseDados(). '##';
      $retorno .= 'URL para downloads dos arquivos XML que contém o resultado da busca simples = '.CtrUtils::getUrlArquivosBuscaSimples(). '##';
      $retorno .= 'A configuração do Driver para acesso a base de dados é = '. Sqls::getConfigBD(). '## ##';
      //----------------------------------------------------------------------------------


      //----------------------------------------------------------------------------------
      //Veririca se a pasta temp existe e se tem permissão de escrita
      $retorno .= '--------------------------------------------------------------------------------- ##';
      $retorno .= 'Dados sobre o diretório temp onde são salvos os resultados da busca simples ##';
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
          $retorno .= 'A pasta temp está com permissão de escrita. ## ##';
          unlink(CtrUtils::getPastaArquivosXML(). $nomeArquivo);
        } else {
          $retorno .= 'A pasta temp NÃO está com permissão de escrita. ## ##';
        }
      } else {
        $retorno .= 'A pasta temp NÃO existe ##';
      }
     //----------------------------------------------------------------------------------



     //----------------------------------------------------------------------------------
     //Verifica conectividade com a base de dados
      $retorno .= '--------------------------------------------------------------------------------- ##';
      $retorno .= 'Dados sobre o diretório base de dados ##';
      $retorno .= '--------------------------------------------------------------------------------- ##';

      $r = CtrUtils::verificaStatusBD();
      if ($r == '-1')
        $retorno .= 'NÃO foi possível conectar-se a base de dados  !!! ##';
      else
        $retorno .= 'O sistema conectou na base de dados com sucesso !!! ## ##';
     //----------------------------------------------------------------------------------


     //----------------------------------------------------------------------------------
     //Executa uma consulta da base de dados e retorna o número de licenças
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

      $retorno .= "Os campos listados a seguir NÃO podem ter registros com valor em branco: ## ##";
      
      $retorno .= "       -> Há ". $campoNumeroLicenca. " licenças com o campo NUMERO_LICENCA em branco. ##";
      $retorno .= "       -> Há ". $campoUFEmpreendimento. " licenças com o campo UF_EMPREENDIMENTO_PRINC em branco. ##";
      $retorno .= "       -> Há ". $campoInstituicaoOrigem. " licenças com o campo INSTITUICAO_DE_ORIGEM em branco. ## ##";

      $retorno .= "Número total de licenças ". $numTotalReg;


      return $retorno;
      }
     //----------------------------------------------------------------------------------
      

//----------------------------------------------------------------------------------
  $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
  $obServer->service($HTTP_RAW_POST_DATA);
?>




