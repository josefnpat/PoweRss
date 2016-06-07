<?php
require('config.php');

$sql = 'SELECT id,data FROM items';
$data = array();
foreach($db->query($sql,PDO::FETCH_ASSOC) as $row){
  // TODO: assumes data is json
  $data[] = json_decode($row['data']);
}

header('Content-Type: application/json'); 
echo json_encode($data, JSON_PRETTY_PRINT);
