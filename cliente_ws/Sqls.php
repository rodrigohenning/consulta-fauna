<?

class Sqls {
//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo retorna a configura��o para acesso a base de dados.
    */
    function getConfigBD() {
      //Configura��o para base de dados Access
   	 // $cfg_dsn = '"seiam", "dwq179xha","ora8"';
	//$cfg_dsn = "seiam:dwq179xha@localhost/ora8";
	//$cfg_dsn = "oci8://seiam:dwq179xha@ora8";
	$cfg_dsn = 'postgres8://pnla:ws_seiam_ac@localhost/DB_SEIAM'; 
      return $cfg_dsn;
    }


//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca simplificada e para busca de totalizadores.
    Recebe como par�metro os filtros que ser�o aplicados
    Retorna o script pronto para ser processado
    */
	function getSqlBuscaLicencaSimplifTotaliz($parametros, $tipologiasArvore, $tipoBusca) {
      $varAux = "";
      $Sql  = "";
      if ($tipoBusca == "S") {
        $Sql  = "select enLi.DATA_VENCIMENTO as xmlDATA_DE_VENCIMENTO, enLi.NUMERO_PROCESSO as xmlNUMERO_DO_PROCESSO, enLi.NUMERO_LICENCA_ORIGINAL as xmlNUMERO_DA_LICENCA, ".
                " enLi.NUMERO_LICENCA as xmlID_DA_LICENCA, enLi.TIPO_LICENCA as xmlTIPO_DA_LICENCA, enLi.SITUACAO_LICENCA as xmlSITUACAO_DA_LICENCA,".
                " enLi.DATA_PROTOCOLO as xmlDATA_DE_PROTOCOLO, enLi.DATA_EMISSAO as xmlDATA_DE_EMISSAO, enLi.TITULO_EMPREENDIMENTO as xmlTITULO_EMPREENDIMENTO, enLi.RIO as xmlNOME_DO_RIO,".
                " enLi.NOME_LOCAL_BACIA_HIDROG as xmlNOME_DO_LOCAL_DA_BACIA, enLi.GRUPO as xmlGRUPO, enLi.SUBGRUPO as xmlSUBGRUPO, enLi.TIPOLOGIA as xmlTIPOLOGIA";

        //essa condi��o s� � satisfeita quando o usu�rio estiver fazendo uma busca simples epec�fica, a partir da busca de totalizadores
        //se a condi��o for verdadeira, ent�o nenhum outro filtro ser� incluido no script.
        $filtroBuscaSimplesEspecifica = $this->retornaFiltroBuscaSimplesEspecif($parametros, "filtroIDLicencasBuscaSimples_". CtrUtils::getSiglaEstado());
        if ( $filtroBuscaSimplesEspecifica != "") {
          $Sql .= " from ws_pnla.\"EN_LICENCAS\" enLi where NUMERO_LICENCA in (".$filtroBuscaSimplesEspecifica.")";
          return $Sql;
        }

      } else {
        $Sql = "select enLi.NUMERO_LICENCA as xmlID_DA_LICENCA";
      }

    //QUANDO A BUSCA � FEITO PELO N�MERO DO PROCESSO
      if ($this->retornaValor($parametros, "tipoTextoBusca") == "nroProcesso") {
        //MONTA A CL�USULA FROM
        $Sql .= " from ws_pnla.\"EN_LICENCAS\" enLi where ";

        //BUSCAR SOMENTE LICEN�AS NO ANO INFORMADO
        $varAux = $this->retornaValor($parametros, "txtAnoNroProcesso");
        $Sql .= " year(enLi.DATA_EMISSAO) = ".$varAux." ";

        //NUMERO DO PROCESSO
        //Quando uma tipologia � selecionada na �rvore de tipologia na interface gr�fica, n�o � obrigat�rio o preenchimento
        //do par�metro termo da busca (txtBusca)
        $varAux = $this->retornaValor($parametros, "txtBusca");
        if ($varAux !=  "") {$Sql .= " and enLi.NUMERO_PROCESSO = '". $varAux."'";}


    //QUANDO A BUSCA � FEITA POR PALAVRA-CHAVE
      } else {
        //Vari�vel que controla se o termo "AND" do script SQL deve ser, ou n�o, acrescentado no script
        $and = "";

        //MONTA A CL�USULA FROM
        $Sql .= " from ws_pnla.\"EN_LICENCAS\" enLi";
        if ($this->existeParametrosArvoreTipologia($tipologiasArvore) == true)
           $Sql .= ", EN_EQUIVALENCIAS enEq";

        $Sql .= " where ";

        //TIPO DO FILTRO QUE DEVER� SER APLICADO SOBRE O TERMO QUE EST� SENDO PESQUISADO.
        $tipoFiltro = $this->retornaValor($parametros, "chkFiltroPlvChave");

        //MONTANDO O SCRIPT PARA O TERMO DA BUSCA (PALAVRA-CHAVE)
        $termoDaBusca = $this->retornaValor($parametros, "txtBusca"); //Texto original digitado pelo usu�rio
        if ($termoDaBusca != "") {
          $termoDaBuscaPreparado = $this->retornaValor($parametros, "txtPalavrasBusca"); //Texto digitado pelo usu�rio, mas sem caracteres especiais e sem alguns tipos de palavras
          $Sql .= $this->montaSQLTermoBusca($termoDaBusca, $termoDaBuscaPreparado, $tipoFiltro);
          $and = " and ";
        }

        //NOME DO MUNIC�PIO DO EMPREENDIMENTO
        $varAux = $this->retornaValor($parametros, "txtMunicipio");
        if ($varAux != "") {
          $Sql .= $and. " enLi.NOME_MUNIC_PRINC_EMPREE = '".$varAux. "' ";
          $and = " and ";
        }

        //NOME DO ESTADO DO EMPREENDIMENTO
        $varAux = $this->montaSQLEstados($parametros, "selEstado");
        if ($varAux != "") {
          $Sql .= $and. " (".$varAux.") ";
          $and = " and ";
        }

        //T�TULO DO EMPREENDIMENTO
        $varAux = $this->retornaValor($parametros, "txtEmpreendimento");
        if ($varAux != "") {
          $Sql .= $and. " enLi.TITULO_EMPREENDIMENTO = '".$varAux. "' ";
          $and = " and ";
        }

        //NOME DO EMPREENDEDOR
        $varAux = $this->retornaValor($parametros, "txtEmpreendedor");
        if ($varAux != "") {
          $Sql .= $and. " enLi.NOME_EMPREENDEDOR = '".$varAux. "' ";
          $and = " and ";
        }

        //TIPO DA LICEN�A
        $varAux = $this->retornaValor($parametros, "selTpoLicenca");
        if ($varAux != "") {
          $Sql .= $and. " enLi.TIPO_LICENCA = '".$varAux. "' ";
          $and = " and ";
        }

        //NOME DO LOCAL DA BACIA HIDROGR�FICA
        $varAux = $this->retornaValor($parametros, "txtBacia");
        if ($varAux != "") {
          $Sql .= $and. " enLi.NOME_LOCAL_BACIA_HIDROG = '".$varAux. "' ";
          $and = " and ";
        }

        //NOME DO RIO
        $varAux = $this->retornaValor($parametros, "txtRio");
        if ($varAux != "") {
          $Sql .= $and. " enLi.RIO = '".$varAux. "' ";
          $and = " and ";
        }

        //SITUA��O DA LINCEN�A
        $varAux = $this->retornaValor($parametros, "selSituacaoLicenca");
        if ($varAux != "") {
          $Sql .= $and. " enLi.SITUACAO_LICENCA = '".$varAux. "' ";
          $and = " and ";
        }

        //DATAS DE EMISS�O
        $dtaEmissaoI = $this->retornaValor($parametros, "dtaInicioEmissao");
        $dtaEmissaoF = $this->retornaValor($parametros, "dtaFimEmissao");
        if (($dtaEmissaoI != "") && ($dtaEmissaoF != "")) {
           $Sql .= $and. " (enLi.DATA_EMISSAO >= #".$dtaEmissaoI."# and enLi.DATA_EMISSAO <= #".$dtaEmissaoF."#) ";
           $and = " and ";
        } else if ($dtaEmissaoI != "") {
            $Sql .= $and. " enLi.DATA_EMISSAO >= #".$dtaEmissaoI."# ";
            $and = " and ";
        } else if ($dtaEmissaoF != "") {
            $Sql .= $and. " enLi.DATA_EMISSAO <= #".$dtaEmissaoF."# ";
            $and = " and ";
        }

        //DATAS DE VENCIMENTO
        $dtaVencimentoI = $this->retornaValor($parametros, "dtaInicioVencimento");
        $dtaVencimentoF = $this->retornaValor($parametros, "dtaFimVencimento");
        if (($dtaVencimentoI != "") && ($dtaVencimentoF != "")) {
           $Sql .= $and. " (enLi.DATA_VENCIMENTO >= #".$dtaVencimentoI."# and enLi.DATA_VENCIMENTO <= #".$dtaVencimentoF."#) ";
           $and = " and ";
        } else if ($dtaVencimentoI != "") {
            $Sql .= $and. " enLi.DATA_VENCIMENTO >= #".$dtaVencimentoI."# ";
            $and = " and ";
        } else if ($dtaVencimentoF != "") {
            $Sql .= $and. " enLi.DATA_VENCIMENTO <= #".$dtaVencimentoF."# ";
            $and = " and ";
        }

        //PORTE
        $prtPequeno = $this->retornaValor($parametros, "chkPortPequeno");
        $prtMedio = $this->retornaValor($parametros, "chkPortMedio");
        $prtGrande = $this->retornaValor($parametros, "chkPortGrande");
        if (($prtPequeno != "") && ($prtMedio != "") && ($prtGrande != "")) {
          $Sql .= $and. " enLi.PORTE in ('".$prtPequeno."','".$prtMedio."','".$prtGrande."','m�dio')"; //foi colocado um m�dio a mais, com acento.
          $and = " and ";
        } else if (($prtPequeno != "") && ($prtMedio != "")) {
            $Sql .= $and. " enLi.PORTE in ('".$prtPequeno."','".$prtMedio."','m�dio')";
            $and = " and ";
        } else if (($prtPequeno != "") && ($prtGrande != "")) {
            $Sql .= $and. " enLi.PORTE in ('".$prtPequeno."','".$prtGrande."')";
            $and = " and ";
        } else if (($prtMedio != "") && ($prtGrande != "")) {
            $Sql .= $and. " enLi.PORTE in ('".$prtMedio."','".$prtGrande."','m�dio')";
            $and = " and ";
        }

        //POTENCIAL POLUIDOR
        $ppdPequeno = $this->retornaValor($parametros, "chkFilPequeno");
        $ppdMedio = $this->retornaValor($parametros, "chkFilMedio");
        $ppdGrande = $this->retornaValor($parametros, "chkFilGrande");
        if (($ppdPequeno != "") && ($ppdMedio != "") && ($ppdGrande != "")) {
          $Sql .= $and. " enLi.PPD in ('".$ppdPequeno."','".$ppdMedio."','".$ppdGrande."','m�dio')"; //foi colocado um m�dio a mais, com acento.
          $and = " and ";
        } else if (($ppdPequeno != "") && ($ppdMedio != "")) {
            $Sql .= $and. " enLi.PPD in ('".$ppdPequeno."','".$ppdMedio."','m�dio')";
            $and = " and ";
        } else if (($ppdPequeno != "") && ($ppdGrande != "")) {
            $Sql .= $and. " enLi.PPD in ('".$ppdPequeno."','".$ppdGrande."')";
            $and = " and ";
        } else if (($ppdMedio != "") && ($ppdGrande != "")) {
            $Sql .= $and. " enLi.PPD in ('".$ppdMedio."','".$ppdGrande."','m�dio')";
            $and = " and ";
        }

        //ARVORE DE TIPOLOGIA
         if ($this->existeParametrosArvoreTipologia($tipologiasArvore) == true)
           $Sql .= $this->montaSQLArvoreTipologia($tipologiasArvore, $and);


      }
      return $Sql;
	}

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca das equival�ncias de tipologias
    Recebe como par�metro o grupo, o subgrupo e a tipologia da licen�a no estado.
    Retorna o script pronto para ser processado
    */
	function getSqlBuscaEquivalenciaTipologia($grupo, $subGrupo, $tipologia) {
      $Sql = " select GRUPO as xmlGRUPO_MMA, SUBGRUPO as xmlSUBGRUPO_MMA,".
             " TIPOLOGIA as xmlTIPOLOGIA_MMA, CLASSE as xmlTIPO_EQUIVALENCIA,".
             " DO as xmlGRUPO_ESTADO, DO as xmlSUBGRUPO_ESTADO".  //O campo subgrupo e grupo s�o iguais pois o campo DO armazena ambas as informa��es: (grupo/subgrupo)
             " from ws_pnla.\"EN_EQUIVALENCIAS\"".
             " where  TE like  '%".$tipologia."%'";
      return $Sql;
	}

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo verifica se existe algum par�metro na matriz de par�metros da �rvore de tipologia
    */
    function existeParametrosArvoreTipologia($tipologiasArvore) {
      $resultado = true;
      if ($tipologiasArvore[0][0] == "") $resultado = false;
      return $resultado;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo � utilizado para montar a parte do SQL relativo a equival�ncia entre as tipologias do Estado com o MMA
    Recebe a matriz de parametros da �rvore de tipologia e monta o SQL
    Recebe o and, vari�vel que informa se �, ou n�o, para colocar o operador AND no inicio do script SQL
    Retorna a parte do SQL relativo a equival�ncia
    */
    function montaSQLArvoreTipologia($tipologiasArvore, $and) {
      $resultado = "";
      for ($i=0; $i < count($tipologiasArvore); $i++) {

        if ($resultado != "") $resultado .= " or ";

        if ($tipologiasArvore[$i][0] == "SUBST-T") {
           $valor1 = $tipologiasArvore[$i][1];
           $resultado .= " (enEq.TIPOLOGIA = '".$valor1."' and enLi.TIPOLOGIA = enEq.TE) ";

        } else if ($tipologiasArvore[$i][0] == "COMPL-T") {
            $valorG = $tipologiasArvore[$i][1];
            $valorS = $tipologiasArvore[$i][2];
            $resultado .= " (enEq.CLASSE = 'COMPL-T' and enEq.GRUPO = '". $valorG."' ".
                            " and enEq.SUBGRUPO = '".$valorS."' ".
                            " and enLi.TIPOLOGIA = enEq.TE) ";

        } else if ($tipologiasArvore[$i][0] == "COMPL-S") {
            $valorG = $tipologiasArvore[$i][1];
            $resultado .= " (enEq.CLASSE = 'COMPL-S' and enEq.GRUPO = '". $valorG."' ".
                            " and enLi.TIPOLOGIA = enEq.TE) ";

        } else if ($tipologiasArvore[$i][0] == "COMPL-G") {
            $resultado .= " (enEq.CLASSE = 'COMPL-G' ".
                            " and enLi.TIPOLOGIA = enEq.TE) ";
        }
      }

      if ($resultado != "") {
         $resultado = $and. " ( ". $resultado. " )";
      }
      return $resultado;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo � utilizado para testar se a base est� on-line e se est� acess�vel
    Retorna um script pronto para ser processado
    O select � feito na tabela EN_EQUIVALENCIA pois � uma tabela que com certeza ir� possuir pelo menos um registro.
    */
	function getSqlTestarConexao() {
	  return "select * from ws_pnla.\"EN_EQUIVALENCIAS\"";
	}

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo � utilizado para pegar o valor de um determinado par�metro na
    matriz de par�metros que � enviada pelo integrador.
    Recebe a matriz de par�metros e o nome do par�metro que se quer saber o valor
    Retorna o valor do par�metro.
    */
    function retornaValor($parametros, $nomeParametroProcurado) {
      $valor = "";
      for ($i=0; $i < count($parametros); $i++) {
        if ($parametros[$i][0] == $nomeParametroProcurado) {
           $valor = $parametros[$i][1];
           $valor = str_replace("'", "",$valor); //retira as aspas simples.
           //$valor = str_replace("\"", "",$valor); //retira as aspas duplas.
           break;
        }
      }
      return $valor;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo � utilizado para montar a parte do SQL relativo ao termo da busca (palavra-chave)
    Recebe a matriz de parametros e monta o SQL
    Retorna trecho do SQL relativo ao termo da busca
    */
    function montaSQLTermoBusca($valorDoTermo, $valorDoTermoPreparado, $tipoFiltro) {
      $SQL = "";
      $operador = "and";
      $termoBusca = "";

      //Frase Exata
      if ($tipoFiltro == "fExata") {
         $SQL = " ((enLi.TITULO_EMPREENDIMENTO like '% ".$valorDoTermo." %' or enLi.TITULO_EMPREENDIMENTO like '".$valorDoTermo." %' or  enLi.TITULO_EMPREENDIMENTO like '% ".$valorDoTermo."' or enLi.TITULO_EMPREENDIMENTO = '".$valorDoTermo."') ";
         $SQL .= " or (enLi.EXTRATO_LICENCA like '% ".$valorDoTermo." %' or enLi.EXTRATO_LICENCA like '".$valorDoTermo." %' or  enLi.EXTRATO_LICENCA like '% ".$valorDoTermo."' or enLi.EXTRATO_LICENCA = '".$valorDoTermo."') ";
         $SQL .= " or (enLi.GRUPO like '% ".$valorDoTermo." %' or enLi.GRUPO like '".$valorDoTermo." %' or  enLi.GRUPO like '% ".$valorDoTermo."' or enLi.GRUPO = '".$valorDoTermo."') ";
         $SQL .= " or (enLi.SUBGRUPO like '% ".$valorDoTermo." %' or enLi.SUBGRUPO like '".$valorDoTermo." %' or  enLi.SUBGRUPO like '% ".$valorDoTermo."' or enLi.SUBGRUPO = '".$valorDoTermo."') ";
         $SQL .= " or (enLi.TIPOLOGIA like '% ".$valorDoTermo." %' or enLi.TIPOLOGIA like '".$valorDoTermo." %' or  enLi.TIPOLOGIA like '% ".$valorDoTermo."' or enLi.TIPOLOGIA = '".$valorDoTermo."')) ";

      } else {
          if ($tipoFiltro == "qPalavra") $operador = " or ";  //qualquer palavra
          else $operador = " and ";                           //todas as palavras

          $termoTitulo = " (";
          $termoExtrado = " or (";
          $termoGrupo = "  or (";
          $termoSubGrupo = " or (";
          $termoTipologia = " or (";

          $termoBusca = split(" ",$valorDoTermoPreparado);         //transforma uma string separada por espa�o em um array
          $tamanhoLista = count($termoBusca);

          for ($i=0; $i < $tamanhoLista; $i++) {

              $termoTitulo .= " enLi.TITULO_EMPREENDIMENTO like '%".$termoBusca[$i]."%' ";
              if ($i != $tamanhoLista - 1) $termoTitulo .= $operador;
              else $termoTitulo .= ") ";

              $termoExtrado .= " enLi.EXTRATO_LICENCA like '%".$termoBusca[$i]."%' ";
              if ($i != $tamanhoLista - 1) $termoExtrado .= $operador;
              else $termoExtrado .= ") ";

              $termoGrupo .= " enLi.GRUPO like '%".$termoBusca[$i]."%' ";
              if ($i != $tamanhoLista - 1) $termoGrupo .= $operador;
              else $termoGrupo .= ") ";

              $termoSubGrupo .= " enLi.SUBGRUPO like '%".$termoBusca[$i]."%' ";
              if ($i != $tamanhoLista - 1) $termoSubGrupo .= $operador;
              else $termoSubGrupo .= ") ";

              $termoTipologia .= " enLi.TIPOLOGIA like '%".$termoBusca[$i]."%' ";
              if ($i != $tamanhoLista - 1) $termoTipologia .= $operador;
              else $termoTipologia .= ") ";

          }
          $SQL = " (".$termoTitulo. $termoExtrado. $termoGrupo. $termoSubGrupo. $termoTipologia. ") ";

      }
      return $SQL;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo � utilizado para montar a parte do SQL relativo aos estados que foram selecionados na
    matriz de par�metros que � enviada pelo integrador.
    Recebe a matriz de parametros e monta o SQL
    Retorna trecho do SQL relativo aos estados pronto
    */
    function montaSQLEstados($parametros, $nomeParametroEstado) {
      $valor = "";
      $primeiroEstado = "sim";
      $SQL = "";
      for ($i=0; $i < count($parametros); $i++) {
        if ($parametros[$i][0] == $nomeParametroEstado) {
           $valor = $parametros[$i][1];
           $valor = str_replace("'", "",$valor); //retira as aspas simples.
           //$valor = str_replace("\"", "",$valor); //retira as aspas duplas.
           if ($primeiroEstado == "sim") {
              $SQL = " enLi.UF_EMPREENDIMENTO_PRINC = '".$valor."' ";
              $primeiroEstado = "nao";
           } else {
             $SQL = $SQL. " or enLi.UF_EMPREENDIMENTO_PRINC = '".$valor."' ";
           }
        }
      }
      return $SQL;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo � utilizado para pegar o valor do par�metro que cont�m o filtro para busca simples espec�fica
    Recebe a matriz de par�metros e o nome do par�metro que se quer saber o valor
    Retorna o valor do par�metro.
    */
    function retornaFiltroBuscaSimplesEspecif($parametros, $nomeParametroProcurado) {
      $valor = "";
      for ($i=0; $i < count($parametros); $i++) {
        if ($parametros[$i][0] == $nomeParametroProcurado) {
           $valor = $parametros[$i][1];
           break;
        }
      }
      return $valor;
    }


//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para verificar se a licen�a possui coordenadas. � utilizado pela busca simples
    Recebe como par�metro a matriz de par�metros
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaSimplesTemCoordenadas($codLicenca) {
      return "select RE_LOCAL_COORDEN.COD_COORDENADA".
             " from ws_pnla.\"RE_LOCAL_EMPREENDIM_LICENCA\", ws_pnla.\"RE_LOCAL_COORDEN\", ws_pnla.\"EN_COORDENADA\"".
             " where RE_LOCAL_EMPREENDIM_LICENCA.NUMERO_LICENCA = '".$codLicenca."'".
             " and RE_LOCAL_COORDEN.COD_LOCALIZACAO = RE_LOCAL_EMPREENDIM_LICENCA.COD_LOCALIZACAO".
             " and EN_COORDENADA.COD_COORDENADA = RE_LOCAL_COORDEN.COD_COORDENADA".
             " and (EN_COORDENADA.LATITUDE <> '' or EN_COORDENADA.LONGITUDE <> '')";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para verificar se a licen�a possui coordenadas. � utilizado pela busca de totalizadores
    Recebe como par�metro a matriz de par�metros
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaTotalizadoresTemCoordenadas($codLicenca) {
      return "select RE_LOCAL_COORDEN.COD_COORDENADA".
             " from ws_pnla.\"RE_LOCAL_EMPREENDIM_LICENCA\", ws_pnla.\"RE_LOCAL_COORDEN\", ws_pnla.\"EN_COORDENADA\"".
             " where RE_LOCAL_EMPREENDIM_LICENCA.NUMERO_LICENCA in (".$codLicenca.")".
             " and RE_LOCAL_COORDEN.COD_LOCALIZACAO = RE_LOCAL_EMPREENDIM_LICENCA.COD_LOCALIZACAO".
             " and EN_COORDENADA.COD_COORDENADA = RE_LOCAL_COORDEN.COD_COORDENADA".
             " and (EN_COORDENADA.LATITUDE <> '' or EN_COORDENADA.LONGITUDE <> '')";
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca completa
    Recebe como par�metro o ID da licen�a
    Retorna o script pronto para ser processado
    */
	function getSqlBuscaLicencaCompleta($codLicenca) {
      $Sql  = " select DATA_VENCIMENTO as xmlDATA_DE_VENCIMENTO,".
              " NUMERO_PROCESSO as xmlNUMERO_DO_PROCESSO,".
              " NUMERO_LICENCA as xmlID_DA_LICENCA,".
              " NUMERO_LICENCA_ORIGINAL as xmlNUMERO_DA_LICENCA,".
              " TIPO_LICENCA as xmlTIPO_DA_LICENCA,".
              " SITUACAO_LICENCA as xmlSITUACAO_DA_LICENCA,".
              " DATA_PROTOCOLO as xmlDATA_DE_PROTOCOLO,".
              " DATA_EMISSAO as xmlDATA_DE_EMISSAO,".
              " URL_TEXTO_INTEGRAL as xmlURL_TEXTO_INTEGRAL,".
              " EXTRATO_LICENCA as xmlEXTRATO_DA_LICENCA,".
              " TITULO_EMPREENDIMENTO as xmlTITULO_EMPREENDIMENTO,".
              " GRUPO as xmlGRUPO,".
              " SUBGRUPO as xmlSUBGRUPO,".
              " TIPOLOGIA as xmlTIPOLOGIA,".
              " DISTRITO_BAIRRO_EMPREEND as xmlDISTRITO_BAIRRO_EMPREENDIMENTO,".
              " CEP_EMPREENDIMENTO as xmlCEP_EMPREENDIMENTO,".
              " UF_EMPREENDIMENTO_PRINC as xmlUF_EMPREENDIMENTO,".
              " RIO as xmlNOME_DO_RIO,".
              " PORTE as xmlPORTE,".
              " PPD as xmlPPD,".
              " CLASSE_EMPREENDIMENTO as xmlCLASSE_EMPREENDIMENTO,".
              " ORIGEM_CLASSE as xmlORIGEM_CLASSE,".
              " NOME_EMPREENDEDOR as xmlNOME_EMPREENDEDOR,".
              " CPF_CNPJ_EMPREENDEDOR as xmlCPF_CNPJ_EMPREENDEDOR,".
              " ENDERECO_EMPREENDEDOR as xmlENDERECO_EMPREENDEDOR,".
              " DISTRITO_BAIRRO_EMPREEN as xmlDISTRITO_BAIRRO_EMPREENDEDOR,".
              " CEP_EMPREENDEDOR as xmlCEP_EMPREENDEDOR,".
              " COD_IBGE_MUNIC_EMPREEND as xmlCOD_IBGE_MUNIC_EMPREENDEDOR,".
              " NOME_MUNICIPIO_EMPREEND as xmlNOME_MUNICIPIO_EMPREENDEDOR,".
              " UF_EMPREENDEDOR as xmlUF_EMPREENDEDOR,".
              " CODIGO_CNAE_EMPREENDEDOR as xmlCODIGO_CNAE_EMPREENDEDOR,".
              " DESC_ATIVIDADE_EMPREENDEDOR as xmlDESC_ATIVIDADE_EMPREENDEDOR,".
              " ENDERECO_EMPREENDIMENTO as xmlENDERECO_EMPREENDIMENTO,".
              " COD_IBGE_MUNIC_PRINC_EMPR as xmlCOD_IBGE_MUNIC_PRINC_EMPREENDIM,".
              " NOME_MUNIC_PRINC_EMPREE as xmlNOME_MUNIC_PRINC_EMPREENDIM,".
              " COD_LOCAL_BACIA_HIDROG as xmlCOD_LOCAL_BACIA_HIDROG,".
              " NOME_LOCAL_BACIA_HIDROG as xmlNOME_LOCAL_BACIA_HIDROG,".
              " COD_ANA_BACIA_HIDROG as xmlCOD_ANA_BACIA_HIDROG,".
              " NOME_ANA_BACIA_HIDROG as xmlNOME_ANA_BACIA_HIDROG".
              " from ws_pnla.\"EN_LICENCAS\" where".
              " NUMERO_LICENCA = '". $codLicenca."'";
      return $Sql;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca completa, elemento OUTROS-MUNICIPIOS
    Recebe como parametro o ID da lincen�a
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaOutrosMunicipios($codLicenca) {
      $Sql  = " select EN_OUTROS_MUNICIPIOS.COD_IBGE as xmlCOD_IBGE_OUTRO_MUNICIPIO,".
              " EN_OUTROS_MUNICIPIOS.NOME as xmlNOME_OUTRO_MUNICIPIO".
              " from ws_pnla.\"EN_OUTROS_MUNICIPIOS\", ws_pnla.\"RE_OUTR_MUNIC_EMPREENDIM_LICENCA\"".
              " where RE_OUTR_MUNIC_EMPREENDIM_LICENCA.NUMERO_LICENCA = '".$codLicenca. "'".
              " and EN_OUTROS_MUNICIPIOS.COD_IBGE =  RE_OUTR_MUNIC_EMPREENDIM_LICENCA.COD_IBGE";
      return $Sql;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca completa, elemento PARAMETROS-DE-DESCRICAO
    Recebe como parametro o ID da lincen�a
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaParametrosDescricao($codLicenca) {
      $Sql  = " select EN_PARAMETRO_DESCRICAO.SIGLA as xmlSIGLA , EN_PARAMETRO_DESCRICAO.NOME as xmlNOME, EN_PARAMETRO_DESCRICAO.VALOR as xmlVALOR,".
              " EN_PARAMETRO_DESCRICAO.UNIDADE_MEDIDA as xmlUNIDADE_MEDIDA".
              " from ws_pnla.\"EN_PARAMETRO_DESCRICAO\", ws_pnla.\"RE_PARAMETRO_EMPREENDIM_LICENCA\"".
              " where RE_PARAMETRO_EMPREENDIM_LICENCA.NUMERO_LICENCA = '".$codLicenca."'".
              "  and EN_PARAMETRO_DESCRICAO.COD_PARAM_DESCRICAO = RE_PARAMETRO_EMPREENDIM_LICENCA.COD_PARAM_DESCRICAO";
      return $Sql;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca completa, elemento COORDENADAS
    Recebe como par�metro o ID da localiza��o
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaCoordenadas($codLocalizacao) {
      $Sql  = " select EN_COORDENADA.LATITUDE as xmlLATITUDE, EN_COORDENADA.LONGITUDE as xmlLONGITUDE".
              " from ws_pnla.\"RE_LOCAL_COORDEN\", ws_pnla.\"EN_COORDENADA\"".
              " where RE_LOCAL_COORDEN.COD_LOCALIZACAO = ".$codLocalizacao.
              " and EN_COORDENADA.COD_COORDENADA = RE_LOCAL_COORDEN.COD_COORDENADA";
      return $Sql;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca completa, elemento LOCALIZACOES
    Recebe como parametro o ID da lincen�a
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaLocalizacoes($codLicenca) {
      $Sql  = "select EN_LOCALIZACAO.COD_LOCALIZACAO as xmlCOD_LOCALIZACAO, EN_LOCALIZACAO.ESCALA as xmlESCALA, EN_LOCALIZACAO.CRITICA as xmlCRITICA, EN_LOCALIZACAO.TIPO as xmlTIPO".
              " from ws_pnla.\"EN_LOCALIZACAO\", ws_pnla.\"RE_LOCAL_EMPREENDIM_LICENCA\"".
              " where RE_LOCAL_EMPREENDIM_LICENCA.NUMERO_LICENCA = '".$codLicenca."'".
              " and  EN_LOCALIZACAO.COD_LOCALIZACAO = RE_LOCAL_EMPREENDIM_LICENCA.COD_LOCALIZACAO";
      return $Sql;
    }

//------------------------------------------------------------------------------------------------------------------------------------------
    /*
    Este m�todo monta o script sql para busca completa, elemento CONDICIONANTES
    Recebe como parametro o ID da lincen�a
    Retorna o script pronto para ser processado
    */
    function getSqlBuscaCondicionantes($codLicenca) {
      $Sql  = "select EN_CONDICIONANTE.DESCRICAO as xmlDESCRICAO, EN_CONDICIONANTE.TIPO as xmlTIPO, EN_CONDICIONANTE.PRAZO as xmlPRAZO, EN_CONDICIONANTE.FREQUENCIA as xmlFREQUENCIA,".
              " EN_CONDICIONANTE.CATEGORIA as xmlCATEGORIA,".
              " EN_CONDICIONANTE.PARAMETRO_LANCAMENTO as xmlPARAMETRO_DO_LANCAMENTO, EN_CONDICIONANTE.VALOR_MAXIMO_LANCAMENTO as xmlVALOR_MAXIMO_LANCAMENTO".
              " from ws_pnla.\"EN_CONDICIONANTE\", ws_pnla.\"RE_CONDICIONANTE_LICENCA\"".
              " where RE_CONDICIONANTE_LICENCA.NUMERO_LICENCA = '".$codLicenca."'".
              "  and EN_CONDICIONANTE.COD_CONDICIONANTE = RE_CONDICIONANTE_LICENCA.COD_CONDICIONANTE";
      return $Sql;
    }
}
?>









