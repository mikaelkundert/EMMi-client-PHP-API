<?php

include "ItellaEmmi.php";

$service_id  = 'my_service_id';
$username    = 'my_username';
$password    = 'my_password';
$service_uri = 'http://mediabank.example.com/emmisoap.asmx';

try {

  $emmi = new ItellaEmmi($service_id, $username, $password, $service_uri);

} catch (Exception $e) {

  print("Couldn't connect to $service_uri with the given crenditals.\n");
  exit();

}

$criterias = array(
  new ItellaEmmiSearchCriteriaModifiedAfter(time() - (60 * 60 * 24 * 7)),
);

/*
Notice that you can also add multiple criteria like this:

$criterias = array(
  new ItellaEmmiSearchCriteriaModifiedAfter(time() - (60 * 60 * 24 * 14)),
  new ItellaEmmiSearchCriteriaModifiedBefore(time() - (60 * 60 * 24 * 7)),
);
*/

$files = $emmi->searchFiles($criterias);

if ($files) {
  print_r($files);
}
else {
  print("No files found.\n");
}

