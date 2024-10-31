<?
class CtrBuscaCompleta {

    var $objSql;
    var $cfg_dsn;
    var $con;
    var $objElementosXML;


//------------------------------------------------------------------------------------------------------------------------------------------
      /*
      Método construtor da classe. Faz as instâncias dos objetos que serão utilizados pelos métodos desta classe
      */
    function CtrBuscaCompleta() {
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
    Método que faz a contrução do XML completo da licença selecionada
    Recebe o ID da licença que deverá ser apresentada
    Retorna o XML relativo a licença selecionada
    */
    function montaXML($codLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaLicencaCompleta($codLicenca));
      $elementoLicenciamento = "";
      if ($rs) {
        if (!$rs->EOF) {
        
          $dataEmissao = $rs->UserDate($rs->fields["xmlDATA_DE_EMISSAO"], 'd/m/Y');
          if ($dataEmissao == "&nbsp;") $dataEmissao = "";
          $dataProtocolo = $rs->UserDate($rs->fields["xmlDATA_DE_PROTOCOLO"], 'd/m/Y');
          if ($dataProtocolo == "&nbsp;") $dataProtocolo = "";
          $dataVencimento = $rs->UserDate($rs->fields["xmlDATA_DE_VENCIMENTO"], 'd/m/Y');
          if ($dataVencimento == "&nbsp;") $dataVencimento = "";
        
        
          //ELEMENTO DADOS-GERAIS
            $elementoTextoDaLicenca = $this->objElementosXML->textoDaLicenca(CtrUtils::escape($rs->fields["xmlURL_TEXTO_INTEGRAL"]), CtrUtils::escape($rs->fields["xmlEXTRATO_DA_LICENCA"]));
          $elementoDadosGerais = $this->objElementosXML->dadosGerais($dataVencimento, CtrUtils::escape($rs->fields["xmlNUMERO_DO_PROCESSO"]), CtrUtils::escape($rs->fields["xmlID_DA_LICENCA"]), CtrUtils::escape($rs->fields["xmlTIPO_DA_LICENCA"]), CtrUtils::escape($rs->fields["xmlSITUACAO_DA_LICENCA"]), $dataProtocolo, $dataEmissao, CtrUtils::escape($rs->fields["xmlNUMERO_DA_LICENCA"]), $elementoTextoDaLicenca);

          //ELEMENTO EMPREENDIMENTO
            $elementoMunicipioPrincipal = $this->objElementosXML->municipioPrincipal(CtrUtils::escape($rs->fields["xmlCOD_IBGE_MUNIC_PRINC_EMPREENDIM"]), CtrUtils::escape($rs->fields["xmlNOME_MUNIC_PRINC_EMPREENDIM"]));
            $elementoOutrosMunicipios = $this->getElementoOutrosMunicipios($codLicenca);
            $elementoBaciaHidroEmpreendimento = $this->objElementosXML->baciaHidrografica(CtrUtils::escape($rs->fields["xmlCOD_LOCAL_BACIA_HIDROG"]), CtrUtils::escape($rs->fields["xmlNOME_LOCAL_BACIA_HIDROG"]), CtrUtils::escape($rs->fields["xmlCOD_ANA_BACIA_HIDROG"]), CtrUtils::escape($rs->fields["xmlNOME_ANA_BACIA_HIDROG"]));
            $elementoParametrosDescricao = $this->getElementoParametrosDescricao($codLicenca);
            $elementoLocalizacoes = $this->getElementoLocalizacoes($codLicenca);
          $elementoEmpreendimento = $this->objElementosXML->empreendimento(CtrUtils::escape($rs->fields["xmlTITULO_EMPREENDIMENTO"]), CtrUtils::escape($rs->fields["xmlGRUPO"]), CtrUtils::escape($rs->fields["xmlSUBGRUPO"]), CtrUtils::escape($rs->fields["xmlTIPOLOGIA"]), CtrUtils::escape($rs->fields["xmlENDERECO_EMPREENDIMENTO"]), CtrUtils::escape($rs->fields["xmlDISTRITO_BAIRRO_EMPREENDIMENTO"]),
                                                                            CtrUtils::escape($rs->fields["xmlCEP_EMPREENDIMENTO"]), CtrUtils::escape($rs->fields["xmlUF_EMPREENDIMENTO"]), CtrUtils::escape($rs->fields["xmlNOME_DO_RIO"]), CtrUtils::escape($rs->fields["xmlPORTE"]), CtrUtils::escape($rs->fields["xmlPPD"]),
                                                                             CtrUtils::escape($rs->fields["xmlCLASSE_EMPREENDIMENTO"]), CtrUtils::escape($rs->fields["xmlORIGEM_CLASSE"]), $elementoMunicipioPrincipal,
                                                                              $elementoOutrosMunicipios, $elementoBaciaHidroEmpreendimento,
                                                                               $elementoParametrosDescricao, $elementoLocalizacoes);

          //ELEMENTO EMPREENDEDOR
            $elementoMunicipioEmpreendedor = $this->objElementosXML->municipioEmpreendedor(CtrUtils::escape($rs->fields["xmlNOME_MUNICIPIO_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlCOD_IBGE_MUNIC_EMPREENDEDOR"]));
            $elementoAtivEmpreendedor = $this->objElementosXML->atividade(CtrUtils::escape($rs->fields["xmlDESC_ATIVIDADE_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlCODIGO_CNAE_EMPREENDEDOR"]));
          $elementoEmpreendedor = $this->objElementosXML->empreendedor(CtrUtils::escape($rs->fields["xmlUF_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlCEP_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlCPF_CNPJ_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlNOME_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlENDERECO_EMPREENDEDOR"]), CtrUtils::escape($rs->fields["xmlDISTRITO_BAIRRO_EMPREENDEDOR"]), $elementoAtivEmpreendedor, $elementoMunicipioEmpreendedor);

          //ELEMEMTO CONDICIONANTES
          $elementoCondicionantes = $this->getElementoCondicionantes($codLicenca);


          //ELEMENTO LICENCIAMENTO
          $elementoLicenciamento = $this->objElementosXML->root_licenciamento(CtrUtils::getSiglaEstado(), $elementoDadosGerais, $elementoEmpreendimento, $elementoEmpreendedor, $elementoCondicionantes);
        }
        $rs->Close();
      }
      $this->con->Close();
      return $elementoLicenciamento;
    }
    
//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a construção do elemento XML "OUTROS-MUNICIPIOS"
    Recebe o código da licença
    Retorna o XML relativo ao elemento "OUTROS-MUNICIPIOS"
    */
    function getElementoOutrosMunicipios($codLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaOutrosMunicipios($codLicenca));
      $elementoMunicipio = "";
      if ($rs) {
        while (!$rs->EOF) {
          $elementoMunicipio .= $this->objElementosXML->municipio(CtrUtils::escape($rs->fields["xmlCOD_IBGE_OUTRO_MUNICIPIO"]), CtrUtils::escape($rs->fields["xmlNOME_OUTRO_MUNICIPIO"]));
          $rs->MoveNext();
         }
        $rs->Close();
      }
      $elementoOutrosMunicipios = $this->objElementosXML->outrosMunicipios($elementoMunicipio);
      return $elementoOutrosMunicipios;
    }
    
//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a construção do elemento XML "PARAMETROS-DE-DESCRICAO"
    Recebe o código da licença
    Retorna o XML relativo ao elemento "PARAMETROS-DE-DESCRICAO"
    */
    function getElementoParametrosDescricao($codLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaParametrosDescricao($codLicenca));
      $elementoParametros = "";
      if ($rs) {
        while (!$rs->EOF) {
          $elementoParametros .= $this->objElementosXML->parametro(CtrUtils::escape($rs->fields["xmlUNIDADE_MEDIDA"]), CtrUtils::escape($rs->fields["xmlVALOR"]), CtrUtils::escape($rs->fields["xmlSIGLA"]), CtrUtils::escape($rs->fields["xmlNOME"]));
          $rs->MoveNext();
        }
        $rs->Close();
      }
      $elementoParametrosDescricao = $this->objElementosXML->parametrosDeDescricao($elementoParametros);
      return $elementoParametrosDescricao;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a construção do elemento XML "COORDENADAS"
    Recebe o código da licença
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
    
//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que faz a construção do elemento XML "LOCALIZACOES"
    Recebe o código da licença
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
    Método que faz a construção do elemento XML "CONDICIONANTES"
    Recebe o código da licença
    Retorna o XML relativo ao elemento "CONDICIONANTES"
    */
    function getElementoCondicionantes($codLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaCondicionantes($codLicenca));
      $elementoCondicionante = "";
      if ($rs) {
        while (!$rs->EOF) {
          $elementoCondicionante .= $this->objElementosXML->condicionante(CtrUtils::escape($rs->fields["xmlDESCRICAO"]), CtrUtils::escape($rs->fields["xmlTIPO"]), CtrUtils::escape($rs->fields["xmlPRAZO"]),CtrUtils::escape($rs->fields["xmlVALOR_MAXIMO_LANCAMENTO"]), CtrUtils::escape($rs->fields["xmlCATEGORIA"]), CtrUtils::escape($rs->fields["xmlFREQUENCIA"]), CtrUtils::escape($rs->fields["xmlPARAMETRO_DO_LANCAMENTO"]));
          $rs->MoveNext();
        }
        $rs->Close();
      }
      $elementoCondicionantes = $this->objElementosXML->condicionantes($elementoCondicionante);
      return $elementoCondicionantes;
    }
}
?>

