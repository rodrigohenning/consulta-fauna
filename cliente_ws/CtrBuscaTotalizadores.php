<?
class CtrBuscaTotalizadores {

    var $objSql;
    var $objElementosXML;
    var $cfg_dsn;
    var $con;

//------------------------------------------------------------------------------------------------------------------------------------------
      /*
      Método construtor da classe. Faz as instâncias dos objetos que serão utilizados pelos métodos desta classe
      */
    function CtrBuscaTotalizadores() {
      $this->objSql = new Sqls();
      $this->objElementosXML = new ElementosXML();
   	  //$db =& ADONewConnection(CtrUtils::getTipoBaseDados());
	  //$db->Connect($this->objSql->getConfigBD());
   	  $db =& ADONewConnection($this->objSql->getConfigBD());
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      $this->con = $db;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que busca o total de licenças no estado
    Recebe a matriz de parametros oriunda do integrador para execução da busca na base de dados
    Retorna o XML com o total de licenças no estado
    */
    function getTotalLicencas($parametros, $tipologiasArvore) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaLicencaSimplifTotaliz($parametros, $tipologiasArvore, "T"));
      $elementoEstado = "";
      if ($rs) {
        $numReg = 0;
        $filtroBuscaSimples = "";
        while (!$rs->EOF) {
          if ($numReg == 0)
            $filtroBuscaSimples .= "'". $rs->fields["xmlID_DA_LICENCA"]. "'";
          else
            $filtroBuscaSimples .= ",'". $rs->fields["xmlID_DA_LICENCA"]. "'";
          $numReg++;
          $rs->MoveNext();
        }
        $rs->Close();
        if ($numReg != 0)
          $elementoEstado .= $this->objElementosXML->estado(CtrUtils::getSiglaEstado(), $numReg, $filtroBuscaSimples, $this->getQtdCoordenadasLicenca($filtroBuscaSimples));
      }
      $this->con->Close();
      if ($elementoEstado == "") {
          $elementoEstado = $this->objElementosXML->estado(CtrUtils::getSiglaEstado(), "0", "", "0");
      }
      $elementoTotalizadores = $this->objElementosXML->totalizadores($elementoEstado);
      return $elementoTotalizadores;
    }
    
//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Método que verifica se a licença possui coordenadas para construção do link mapa de coordenadas
    Recebe os códigos das licenças que estão sendo contabilizadas para o totalizador.
    Retorna o número de coordenadas da licença
    */
    function getQtdCoordenadasLicenca($IdsLicenca) {
      $rs = $this->con->Execute($this->objSql->getSqlBuscaTotalizadoresTemCoordenadas($IdsLicenca));
      $valorAtributo = "0";
      if ($rs) {
        $valorAtributo = $rs->RecordCount();
        $rs->Close();
      }
      return $valorAtributo;
    }
}
?>

