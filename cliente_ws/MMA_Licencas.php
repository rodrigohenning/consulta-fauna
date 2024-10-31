<?
/*
    Web Service de busca do Portal Nacional de Licenciamento Ambietal - PNLA
*/
require_once('lib/nusoap.php');

include "Sqls.php";
include "ElementosXML.php";
include "CtrUtils.php";
include "CtrBuscaSimplificada.php";
include "CtrBuscaCompleta.php";
include "CtrBuscaTotalizadores.php";
include('adodb/adodb.inc.php');


/*
  Definição dos serviços e assinaturas para geração do WSDL
*/
	$obServer = new soap_server();
    $obServer->configureWSDL('MMA_Licencas ', CtrUtils::getUrlServico().'?wsdl');
    $obServer->wsdl->addComplexType(
      'parametros',
      'complexType',
      'struct',
      'all',
      '',
      array(
        'nomeParametro' => array('name' => 'nomeParametro', 'type' => 'xsd:string'),
        'valorParametro' => array('name' => 'valorParametro', 'type' => 'xsd:string')
      )
    );
    $obServer->wsdl->addComplexType(
      'tipologiasArvore',
      'complexType',
      'struct',
      'all',
      '',
      array(
        'classeDaEquivalencia' => array('name' => 'classeDaEquivalencia', 'type' => 'xsd:string'),
        'valor1' => array('name' => 'valor1', 'type' => 'xsd:string'),
        'valor2' => array('name' => 'valor2', 'type' => 'xsd:string')
      )
    );
    $obServer->register('buscaSimplificada',array('parametros' => 'tns:parametros', 'tipologiasArvore' => 'tns:tipologiasArvore'),array('return' => 'xsd:string'),'urn:MMA_Licencaswsdl','urn:MMA_Licencaswsdl#parametros','rpc','encoded','Executa a busca resumida da licença. Retorna uma string (XML) com os dados resumidos das licenças e suas respectivas equivalências.');
    $obServer->register('buscaCompleta',array('parametros' => 'xsdString'),array('return' => 'xsd:string'),'urn:MMA_Licencaswsdl','urn:MMA_Licencaswsdl#parametros','rpc','encoded','Executa a busca de todas as informações de uma determinada licença. Retorna uma string contendo o total de licenças do estado');
    $obServer->register('buscaTotalizadores',array('parametros' => 'tns:parametros', 'tipologiasArvore' => 'tns:tipologiasArvore'),array('return' => 'xsd:string'),'urn:MMA_Licencaswsdl','urn:MMA_Licencaswsdl#parametros','rpc','encoded','Executa a busca de todas as informações de uma determinada licença. Retorna uma string contendo o total de licenças do estado');


//----------------------------------------------------------------------------------
    /*
    Executa a busca resumida da licença.
    Retorna um XML contendo os dados resumidos das licenças e suas respectivas equivalências.
    */
	function buscaSimplificada($parametros, $tipologiasArvore) {
      CtrUtils::deletaArquivosDirTemp();
	  $testeConexaoBD = CtrUtils::verificaStatusBD();
	  if ($testeConexaoBD == "") {
  	    $objCtrBuscaSimplificada = new CtrBuscaSimplificada();
        $XML = $objCtrBuscaSimplificada->montaXML($parametros, $tipologiasArvore);
  	    return $XML;
      } else {
        return $testeConexaoBD;
      }
	}

//----------------------------------------------------------------------------------
    /*
    Faz a busca de todas as informações de uma determinada licença.
    Retorna uma string contendo o XML com as informações da licença
    */
    function buscaCompleta($parametros) {
	  $testeConexaoBD = CtrUtils::verificaStatusBD();
	  if ($testeConexaoBD == "") {
    	$CtrBuscaCompleta = new CtrBuscaCompleta();
        $XML = $CtrBuscaCompleta->montaXML($parametros);
        return $XML;
      } else {
        return $testeConexaoBD;
      }
    }

//----------------------------------------------------------------------------------
    /*
    Faz a contabilização do número de licenças que atendem aos parâmetros passados
    Retorna uma string contendo o total de licenças do estado
    */
    function buscaTotalizadores($parametros, $tipologiasArvore) {
	  $testeConexaoBD = CtrUtils::verificaStatusBD();
	  if ($testeConexaoBD == "") {
  	    $objCtrBuscaTotalizadores = new CtrBuscaTotalizadores();
        $totalLicencas = $objCtrBuscaTotalizadores->getTotalLicencas($parametros, $tipologiasArvore);
  	    return $totalLicencas;
      } else {
        return $testeConexaoBD;
      }
    }

//----------------------------------------------------------------------------------
  $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
  $obServer->service($HTTP_RAW_POST_DATA);
?>
