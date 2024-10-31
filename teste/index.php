<?php

header('Content-Type: text/html; charset=UTF-8');

?>
<html>
<head>
  <title>IMAC/BOLETOS </title>
  <!-- Bootstrap -->
  <link rel="stylesheet" href="bootstrap-4.1.3-dist/css/bootstrap.min.css"> 

  <style type="text/css">
  input,textarea, button{
    margin: 10px 0 0px 0;
  }
  </style>
</head>

<body>
	<div class="container" style="margin-top: 50px;"><h2>Consultar Boletos 1.0</h2>
    <div class="row">
      <div class="col-sm-6">
        <!-- <form action="enviaContato.php" method="post"> -->
        <form action="index.php" method="post">
          <div class="form-group">
            <label for="Tipoconsulta">Tipo de Consulta:</label> 
            <div>
              <select id="Tipoconsulta" name="Tipoconsulta" required="required" class="custom-select">
                <option value="">Selecione</option>
                <option value=2>Código de barra do documento DAE</option>
                <option value=1>Número da Licença SISPASS</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="Documentoconsulta">Número da licença ou Código de barra do DAE:</label> 
            <input id="Documentoconsulta" name="Documentoconsulta" type="number" aria-describedby="DocumentoconsultaHelpBlock" required="required" class="form-control"> 
            <span id="DocumentoconsultaHelpBlock" class="form-text text-muted">No caso do  Código de barra do DAE remover último digito de cada bloco.</span>
          </div> 
          <div class="form-group">
            <button name="submit" type="submit" class="btn btn-primary">Consultar</button>
          </div>
        </form>

      </div>

    </div>

  </div>
</body>

<?php
/*
 *  06/07/2021 23:02:48 Conquista - Rio Branco - Acre 
 *
 *  WSDL client conecta ao webservice sefaz para verificar boletos.
 *
 *  Service: WSDL
 *  Payload: document/literal
 *  Transport: http
 *  Authentication: Rodrigo Henning - RTech
 */
  require_once('lib/nusoap.php');


  if(isset($_POST['Documentoconsulta'])) {  


    $codigo=(string)$_POST['Documentoconsulta'];
    $tipo=$_POST['Tipoconsulta']; 

    $proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
    $proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
    $proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
    $proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
    //$useCURL = isset($_POST['usecurl']) ? $_POST['usecurl'] : '0';

    $client = new nusoap_client('http://seiam.ac.gov.br:8080/seiam/cliente_ws/', 'wsdl',
    $proxyhost, $proxyport, $proxyusername, $proxypassword);

    $client ->setEndpoint('http://seiam.ac.gov.br:8080/seiam/cliente_ws/'); 
    // $client->soap_defencoding = 'UTF-8'; 
    // $client->decode_utf8 = false;
    // $client->setUseCurl($useCURL);

//858100000005 800000012115 403008240523 137395140000
//85810000000800000012114030082405213739514000
//6296700
// Doc/lit parameters get wrapped
    $param = array('Documentoconsulta' =>$codigo, 'Tipoconsulta' =>$tipo);

    $result = $client->call('Execute', $param);

 // Check for errors
    $err = $client->getError();
    if ($err) {
   // Display the error
     echo '<h2>Error</h2><pre>' . $err . '</pre>';
   } else {
   // Display the result
     echo '<h2>Resultado da consulta</h2>';
     echo "<br/><b>Mensagem  SEFAZ:</b> ";
     echo utf8_encode($result['Statusdae']);
     echo utf8_encode($result['Msg_retorno']);
   }

 } else {  
  # retorna null 
 } 


//echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>