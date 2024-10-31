<?
class CtrBuscaSimplificada {

    var $objSql;
    var $cfg_dsn;
    var $con;
    var $objElementosXML;

//------------------------------------------------------------------------------------------------------------------------------------------
      /*
      Método construtor da classe. Faz as instâncias dos objetos que serão utilizados pelos métodos desta classe
      */
    function CtrBuscaSimplificada() {
      $this->objSql = new Sqls();
   	  //$db =& ADONewConnection(CtrUtils::getTipoBaseDados());
	  //$db->Connect($this->objSql->getConfigBD());
   	  $db =& ADONewConnection($this->objSql->getConfigBD());
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      $this->con = $db;
      $this->objElementosXML = new ElementosXML();
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a contrução do XML simplificado das licenças do estado.
    Recebe a matriz de parametros oriunda do integrador para execução da busca na base de dados
    Retorna o XML relativo as licenças do estado.
    */
    function montaXML($parametros, $tipologiasArvore) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaLicencaSimplifTotaliz($parametros, $tipologiasArvore, "S"));
      $nomeArquivo = $this->objSql->retornaValor($parametros, "txtIDSessaoCliente"). ".xml";
      $elementoLicenciamento = "";
      if ($rs) {
        $totalLicenca = $rs->RecordCount();
        //cria o arquivo onde será armazenado o resultado da busca
      	$fin = fopen(CtrUtils::getPastaArquivosXML(). $nomeArquivo, "w+");
      	$elementoInicial = $this->objElementosXML->instituicaoDeOrigem_Abrir(CtrUtils::getSiglaEstado(), $totalLicenca);
        fputs($fin, $elementoInicial, strlen($elementoInicial));
        while (!$rs->EOF) {
          $dataEmissao = $rs->UserDate($rs->fields["xmlDATA_DE_EMISSAO"], 'd/m/Y');
          if ($dataEmissao == "&nbsp;") $dataEmissao = "";
          $dataProtocolo = $rs->UserDate($rs->fields["xmlDATA_DE_PROTOCOLO"], 'd/m/Y');
          if ($dataProtocolo == "&nbsp;") $dataProtocolo = "";
          $dataVencimento = $rs->UserDate($rs->fields["xmlDATA_DE_VENCIMENTO"], 'd/m/Y');
          if ($dataVencimento == "&nbsp;") $dataVencimento = "";

          $elementoDadosGerais = $this->objElementosXML->dadosGerais($dataVencimento, CtrUtils::escape($rs->fields["xmlNUMERO_DO_PROCESSO"]), $rs->fields["xmlID_DA_LICENCA"], CtrUtils::escape($rs->fields["xmlTIPO_DA_LICENCA"]), CtrUtils::escape($rs->fields["xmlSITUACAO_DA_LICENCA"]), $dataProtocolo, $dataEmissao, CtrUtils::escape($rs->fields["xmlNUMERO_DA_LICENCA"]), "");
          $elementoLocalizacoes = $this->getElementoLocalizacoes($rs->fields["xmlID_DA_LICENCA"]);
          $atributoTemCoordenadasParaConstrucaoMapa = $this->getAtributoCoordenadas($rs->fields["xmlID_DA_LICENCA"]);
          $elementoInformacoesAdicionais = $this->objElementosXML->informacoesGerais(CtrUtils::escape($rs->fields["xmlTITULO_EMPREENDIMENTO"]), CtrUtils::escape($rs->fields["xmlNOME_DO_RIO"]), CtrUtils::escape($rs->fields["xmlNOME_DO_LOCAL_DA_BACIA"]), CtrUtils::escape($rs->fields["xmlTIPOLOGIA"]), $atributoTemCoordenadasParaConstrucaoMapa);
          $elementoTipologiaEq = $this->getElementoTipologia($rs->fields["xmlGRUPO"], $rs->fields["xmlSUBGRUPO"], $rs->fields["xmlTIPOLOGIA"]);
          $elementoLicenciamento = $this->objElementosXML->licenciamento($elementoDadosGerais, $elementoLocalizacoes, $elementoInformacoesAdicionais, $elementoTipologiaEq);
          fputs($fin, $elementoLicenciamento, strlen($elementoLicenciamento));
          $rs->MoveNext();
        }
        $rs->Close();
      }
      $this->con->Close();
      $elementoFinal = $this->objElementosXML->instituicaoDeOrigem_Fechar();
 	  fputs($fin, $elementoFinal, strlen($elementoFinal));
 	  fclose($fin);
      return CtrUtils::getUrlArquivosBuscaSimples(). $nomeArquivo;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a contrução do XML com as equivalêncas de tipologia (MMA x Estado)
    Recebe o grupo, o subgrupo e a tipologia da licença no estado
    Retorna o XML relativo as equivalências da tipologia no MMA
    */
    function getElementoTipologia($grupo, $subGrupo, $tipologia) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaEquivalenciaTipologia($grupo, $subGrupo, $tipologia));
      $elementoEquivalencia = "";
      if ($rs) {
        while (!$rs->EOF) {
          if ($grupo == "") $grupo = CtrUtils::escape($rs->fields["xmlGRUPO_ESTADO"]);
          if ($subGrupo == "") $subGrupo = CtrUtils::escape($rs->fields["xmlSUBGRUPO_ESTADO"]);
          $elementoEquivalencia .= $this->objElementosXML->equivalencia(CtrUtils::escape($rs->fields["xmlGRUPO_MMA"]), CtrUtils::escape($rs->fields["xmlSUBGRUPO_MMA"]), CtrUtils::escape($rs->fields["xmlTIPOLOGIA_MMA"]), $grupo, $subGrupo, $tipologia, CtrUtils::escape($rs->fields["xmlTIPO_EQUIVALENCIA"]));
          $rs->MoveNext();
         }
        $rs->Close();
      }
      $elementoTipologiaEq = $this->objElementosXML->tipologiaEquivalente($elementoEquivalencia);
      return $elementoTipologiaEq;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que verifica se a licença possui coordenadas para construção do mapa de coordenadas
    Recebe a matriz de parametros oriunda do integrador para execução da busca na base de dados
    Retorna o valor da URL do estado caso haja coordenada, ou retorna vazio quando não há coordenadas
    */
    function getAtributoCoordenadas($codLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaSimplesTemCoordenadas($codLicenca));
      $valorAtributo = "";
      if ($rs) {
        if ($rs->RecordCount() > 0) {
          $valorAtributo = CtrUtils::getUrlServico();
        }
        $rs->Close();
      }
      return $valorAtributo;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a construção do elemento XML "LOCALIZACOES"
    Recebe o código da licenca
    Retorna o XML relativo ao elemento "LOCALIZACOES"
    */
    function getElementoLocalizacoes($codLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaLocalizacoes($codLicenca));
      $elementoLocalizacao = "";
      if ($rs) {
        while (!$rs->EOF) {
          $elementoCoordenadas = "";
          $elementoCoordenadas = $this->getElementoCoordenadas(CtrUtils::escape($rs->fields["xmlCOD_LOCALIZACAO"]),CtrUtils::escape($rs->fields["xmlTIPO"]));
          $elementoLocalizacao .= $this->objElementosXML->localizacao(CtrUtils::escape($rs->fields["xmlESCALA"]), CtrUtils::escape($rs->fields["xmlCRITICA"]), $elementoCoordenadas);
          $rs->MoveNext();
        }
        $rs->Close();
      }
      $elementoLocalizacoes = $this->objElementosXML->localizacoes($elementoLocalizacao);
      return $elementoLocalizacoes;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a construção do elemento XML "COORDENADAS"
    Recebe o código da licenca
    Retorna o XML relativo ao elemento "COORDENADAS"
    */
    function getElementoCoordenadas($codLocalizacao, $tipo) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaCoordenadas($codLocalizacao));
      $elementoCoordenada = "";
      if ($rs) {
        while (!$rs->EOF) {
          $elementoCoordenada .= $this->objElementosXML->coordenada(CtrUtils::escape($rs->fields["xmlLATITUDE"]),CtrUtils::escape($rs->fields["xmlLONGITUDE"]));
          $rs->MoveNext();
        }
        $rs->Close();
      }
      $elementoCoordenadas = $this->objElementosXML->coordenadas($tipo, $elementoCoordenada);
      return $elementoCoordenadas;
    }

}
?>

