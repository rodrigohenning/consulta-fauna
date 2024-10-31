<?
class ElementosXML {

 //------------------------------------------------------------------------------------------------------------------------------------------
 //ELEMENTO DADOS GERAIS
      function textoDaLicenca($UrlTextoLicenca, $ExtratoDaLicenca) {
        return " <TEXTO-DA-LICENCA URL-TEXTO-INTEGRAL=\"".$UrlTextoLicenca."\" EXTRATO-DA-LICENCA=\"".$ExtratoDaLicenca."\"/> ";
      }
      
      //O número da licença é um ID, uma espécie de chave-primária. Já o número real da licença é o número que ela tem no estado e pode se repetir
      //ou seja, pode haver duas licenças com o mesmo número.
      function dadosGerais($DataVencimento, $NumeroProcesso, $IdLincenca, $TipoLicenca,
                                $SituacaoDaLicenca, $DataDeProcolo, $DataDeEmissao, $NumeroLicenca, $ElementoTextoDaLicenca) {
        $Elemento = " <DADOS-GERAIS ID-DA-LICENCA=\"".$IdLincenca."\" DATA-DE-VENCIMENTO=\"".$DataVencimento."\" NUMERO-DO-PROCESSO=\"".$NumeroProcesso."\" NUMERO-DA-LICENCA=\"".$NumeroLicenca."\" TIPO-DA-LICENCA=\"".$TipoLicenca."\" SITUACAO-DA-LICENCA=\"".$SituacaoDaLicenca."\" DATA-DE-PROTOCOLO=\"".$DataDeProcolo."\" DATA-DE-EMISSAO=\"".$DataDeEmissao."\">";
        $Elemento = $Elemento. $ElementoTextoDaLicenca. "</DADOS-GERAIS> ";
        return $Elemento;
      }

 //------------------------------------------------------------------------------------------------------------------------------------------
 //ELEMENTO EMPREENDIMENTO
      function municipioPrincipal($CodigoDoIBGE, $Nome) {
        return " <MUNICIPIO-PRINCIPAL NOME=\"".$Nome."\" CODIGO-IBGE=\"".$CodigoDoIBGE."\"/> ";
      }
      
      function municipio($CodigoDoIBGE, $Nome) {
        return " <MUNICIPIO NOME=\"".$Nome."\" CODIGO-IBGE=\"".$CodigoDoIBGE."\"/> ";
      }
      
      function outrosMunicipios($ElementoMunicipio) {
        return "<OUTROS-MUNICIPIOS>". $ElementoMunicipio."</OUTROS-MUNICIPIOS>";
      }

      function baciaHidrografica($CodigoLocal, $NomeLocal, $CodigoANA, $NomeANA) {
        return " <BACIA-HIDROGRAFICA CODIGO-DO-LOCAL=\"".$CodigoLocal."\" NOME-DO-LOCAL=\"".$NomeLocal."\" CODIGO-ANA=\"".$CodigoANA."\" NOME-ANA=\"".$NomeANA."\"/> ";
      }

      function parametro($UnidadeDeMedida, $ValorDoParametro, $SiglaDoParametro, $NomeDoParametro) {
        return " <PARAMETRO SIGLA-DO-PARAMETRO=\"".$SiglaDoParametro."\" NOME-DO-PARAMETRO=\"".$NomeDoParametro."\" VALOR-DO-PARAMETRO=\"".$ValorDoParametro."\" UNIDADE-DE-MEDIDA=\"".$UnidadeDeMedida."\" /> ";
      }

      function parametrosDeDescricao($ElementoParametro) {
        return " <PARAMETROS-DE-DESCRICAO>" .$ElementoParametro. " </PARAMETROS-DE-DESCRICAO> ";
      }

      function coordenada($Latitude, $Longitude) {
        return " <COORDENADA LATITUDE=\"".$Latitude."\" LONGITUDE=\"".$Longitude."\"/> ";
      }

      function coordenadas($Tipo, $ElementoCoordenada) {
        $Elemento = " <COORDENADAS TIPO=\"".$Tipo."\"> ";
        $Elemento = $Elemento. $ElementoCoordenada. " </COORDENADAS> ";
        return $Elemento;
      }

      function localizacao($Escala, $Critica, $ElementoCoordenadas) {
        $Elemento = " <LOCALIZACAO ESCALA=\"".$Escala."\" CRITICA=\"".$Critica."\"> ";
        $Elemento = $Elemento. $ElementoCoordenadas. " </LOCALIZACAO> ";
        return $Elemento;
      }

      function localizacoes($ElementoLocalizacao) {
        $Elemento = " <LOCALIZACOES> ".$ElementoLocalizacao. " </LOCALIZACOES> ";
        return $Elemento;
      }

      function empreendimento($Titulo, $Grupo, $SubGrupo, $Tipologia, $Endereco, $DistritoBairro, $Cep, $Uf, $Rio, $Porte, $PPD,
                               $ClasseEmpreendimento, $OrigemClasse, $ElementoMunicipioPrincipal, $ElementoOutrosMunicipios,
                                $ElementoBaciaHidrografica, $ElementoParametrosDeDescricao, $ElementoLocalizacoes) {

        $Elemento = " <EMPREENDIMENTO TITULO=\"".$Titulo."\" GRUPO=\"".$Grupo."\"
                                        SUBGRUPO=\"".$SubGrupo."\" TIPOLOGIA=\"".$Tipologia."\"
                                        ENDERECO=\"".$Endereco."\" DISTRITO-BAIRRO=\"".$DistritoBairro."\" CEP=\"".$Cep."\" UF=\"".$Uf."\"
                                        RIO=\"".$Rio."\" PORTE=\"".$Porte."\" PPD=\"".$PPD."\" CLASSE-DO-EMPREENDIMENTO=\"".$ClasseEmpreendimento."\"
                                        ORIGEM-DA-CLASSE=\"".$OrigemClasse."\"> ";
        $Elemento =  $Elemento. $ElementoMunicipioPrincipal. $ElementoOutrosMunicipios. $ElementoBaciaHidrografica. $ElementoParametrosDeDescricao. $ElementoLocalizacoes;
        $Elemento = $Elemento. " </EMPREENDIMENTO> ";
        return $Elemento;
      }
 //-----------------------------------------------------------------------------------------------------------------------------------------
 //ELEMENTO EMPREENDEDOR
      function atividade($Descricao, $CodigoCNAE) {
        return " <ATIVIDADE CODIGO-CNAE=\"".$CodigoCNAE."\" DESCRICAO=\"".$Descricao."\" /> ";
      }
      
      function municipioEmpreendedor($Nome, $CodigoDoIBGE) {  //esse município é identico ao município do empreendimento
        return $this->municipio($CodigoDoIBGE, $Nome);
      }
      
      function empreendedor($Uf, $Cep, $Codigo, $Nome, $Endereco, $DistritoBairro, $ElementoAtividade, $ElementoMunicipioEmpreendedor) {
        $Elemento = " <EMPREENDEDOR CODIGO=\"".$Codigo."\" NOME=\"".$Nome."\" ENDERECO=\"".$Endereco."\" DISTRITO-BAIRRO=\"".$DistritoBairro."\" CEP=\"".$Cep."\" UF=\"".$Uf."\"> ";
        $Elemento = $Elemento. $ElementoAtividade. $ElementoMunicipioEmpreendedor. " </EMPREENDEDOR> ";
        return $Elemento;
      }
 //-----------------------------------------------------------------------------------------------------------------------------------------
 //ELEMENTO CONDICIONANTES
      function condicionante($Descricao, $Tipo, $Prazo, $ValorMaximoDoLancamento, $Categoria, $Frequencia, $ParametroDoLancamento) {
        return " <CONDICIONANTE DESCRICAO=\"".$Descricao."\" TIPO=\"".$Tipo."\" PRAZO=\"".$Prazo."\" FREQUENCIA=\"".$Frequencia."\" CATEGORIA=\"".$Categoria."\" PARAMETRO-LANCAMENTO=\"".$ParametroDoLancamento."\" VALOR-MAXIMO-LANCAMENTO=\"".$ValorMaximoDoLancamento."\" /> ";
      }
      
      function condicionantes($ElementoCondicionante) {
        return " <CONDICIONANTES> ".$ElementoCondicionante." </CONDICIONANTES> ";
      }
      
 //-----------------------------------------------------------------------------------------------------------------------------------------
 //ELEMENTO ROOT LICENCIAMENTO
      function root_licenciamento($InstituicaoDeOrigem, $ElementoDadosGerais, $ElementoEmpreendimento, $ElementoEmpreendedor, $ElementoCondicionantes) {
        $Elemento = " <LICENCIAMENTO INSTITUICAO-DE-ORIGEM=\"".$InstituicaoDeOrigem."\"> ";
        $Elemento = $Elemento. $ElementoDadosGerais. $ElementoEmpreendimento. $ElementoEmpreendedor. $ElementoCondicionantes." </LICENCIAMENTO> ";
        return $Elemento;
      }

 //FIM DOS ELEMENTOS PARA CONSTRUÇÃO DO XML COMPLETO
 //-----------------------------------------------------------------------------------------------------------------------------------------

 //INICIO DOS MÉTODOS PARA CONSTRUÇÃO DO XML SIMPLIFICADO
 //-----------------------------------------------------------------------------------------------------------------------------------------

       function equivalencia($grupoMMA, $subGrupoMMA, $tipologiaMMA, $grupoEstado, $subGrupoEstado, $tipologiaEstado, $tipoEquivalencia) {
        $Elemento = " <EQUIVALENCIA GRUPO-DA-TIPOLOGIA-NO-MMA=\"".$grupoMMA."\" ";
        $Elemento = $Elemento. " SUBGRUPO-DA-TIPOLOGIA-NO-MMA=\"".$subGrupoMMA."\" ";
        $Elemento = $Elemento. " DESCRICAO-DA-TIPOLOGIA-NO-MMA=\"".$tipologiaMMA."\" ";
        $Elemento = $Elemento. " GRUPO-DA-TIPOLOGIA-NO-ESTADO=\"".$grupoEstado."\" ";
        $Elemento = $Elemento. " SUBGRUPO-DA-TIPOLOGIA-NO-ESTADO=\"".$subGrupoEstado."\" ";
        $Elemento = $Elemento. " EQUIVALENCIA-DA-TIPOLOGIA-NO-ESTADO=\"".$tipologiaEstado."\" ";
        $Elemento = $Elemento. " TIPO-DA-EQUIVALENCIA=\"".$tipoEquivalencia."\"/> ";
        return $Elemento;
      }

 //-----------------------------------------------------------------------------------------------------------------------------------------
      function tipologiaEquivalente($elementoEquivalencia) {
        return 	" <TIPOLOGIA-EQUIVALENTE> ".$elementoEquivalencia." </TIPOLOGIA-EQUIVALENTE> ";
      }

 //-----------------------------------------------------------------------------------------------------------------------------------------
      function licenciamento($elementoDadosGerais, $elementoLocalizacoes, $elementoInformacoesGerais, $elementoTipologiaEquivalente) {
        return " <LICENCIAMENTO> ". $elementoDadosGerais. $elementoLocalizacoes. $elementoInformacoesGerais. $elementoTipologiaEquivalente." </LICENCIAMENTO> ";
      }

 //-----------------------------------------------------------------------------------------------------------------------------------------
      function informacoesGerais($tituloDaLicenca, $rio, $NomeLocalBacia, $tipologia, $URLEstado) {
       $Elemento = " <INFORMACOES-ADICIONAIS TITULO-EMPREENDIMENTO=\"".$tituloDaLicenca."\" NOME-DO-RIO=\"".$rio."\" NOME-DO-LOCAL-DA-BACIA=\"".$NomeLocalBacia."\" TIPOLOGIA=\"".$tipologia."\"";
       if ($URLEstado != "")
         $Elemento = $Elemento. " URL-DO-SERVICO-NO-ESTADO=\"".$URLEstado."\" ";
       $Elemento = $Elemento. " /> ";
       return $Elemento;
      }

 //-----------------------------------------------------------------------------------------------------------------------------------------
      function instituicaoDeOrigem_Abrir($instituicaoDeOrigem, $totalDeLicencas) {
        $Elemento = " <INSTITUICAO-DE-ORIGEM NOME-DA-INSTITUICAO-DE-ORIGEM=\"".$instituicaoDeOrigem."\" TOTAL-DE-LICENCAS=\"".$totalDeLicencas."\"> ";
        return $Elemento;
      }

 //-----------------------------------------------------------------------------------------------------------------------------------------
      function instituicaoDeOrigem_Fechar() {
        $Elemento = " </INSTITUICAO-DE-ORIGEM> ";
        return $Elemento;
      }



 //INICIO DOS MÉTODOS PARA CONSTRUÇÃO DO XML DOS TOTALIZADORES
 //-----------------------------------------------------------------------------------------------------------------------------------------
      function estado($siglaDoEstado, $totalLicencas, $filtroBuscaSimples, $numCoordenadas) {
        $Elemento = " <ESTADO SIGLA-DO-ESTADO=\"".$siglaDoEstado."\" TOTAL-DE-LICENCAS=\"".$totalLicencas."\" FILTRO-BUSCA-SIMPLES=\"".$filtroBuscaSimples."\" NUMERO-DE-COORDENADAS=\"".$numCoordenadas."\"/> ";
        return $Elemento;
      }

      function totalizadores($elementoEstado) {
        $Elemento = " <TOTALIZADORES> ". $elementoEstado. " </TOTALIZADORES> ";
        return $Elemento;
      }
}
?>
