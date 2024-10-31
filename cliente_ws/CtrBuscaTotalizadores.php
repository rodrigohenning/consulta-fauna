<?
class CtrBuscaTotalizadores {

    var $objSql;
    var $objElementosXML;
    var $cfg_dsn;
    var $con;

//------------------------------------------------------------------------------------------------------------------------------------------
      /*
      M�todo construtor da classe. Faz as inst�ncias dos objetos que ser�o utilizados pelos m�todos desta classe
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
    M�todo que busca o total de licen�as no estado
    Recebe a matriz de parametros oriunda do integrador para execu��o da busca na base de dados
    Retorna o XML com o total de licen�as no estado
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
    M�todo que verifica se a licen�a possui coordenadas para constru��o do link mapa de coordenadas
    Recebe os c�digos das licen�as que est�o sendo contabilizadas para o totalizador.
    Retorna o n�mero de coordenadas da licen�a
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

