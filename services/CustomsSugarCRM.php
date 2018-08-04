<?php

namespace App;
use SoapClient;
$client;

/*
 * url = https://crm.test.open3s.int
 * 192.168.251.12
 * user = devel
 * password = it4up
 */
$urlSugar = "http://www.sugarcrm.com/service/v4_1/soap.php?wsdl";

require_once("./nusoap/lib/nusoap.php");

$client = new nusoap_client($urlSugar, 'wsdl');

print_r($client);

$err = $client->getError();

if($err) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    exit();
}

/*function getClient() {  
    $client = new SoapClient($urlSugar);
    
    return $client;
}*/

$result = $client.getClient();
echo $result;


function setProjectesClient($client) {
    this.$client = $client;
    
    
}



