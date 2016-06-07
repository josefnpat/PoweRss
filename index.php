<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/mui.min.css" rel="stylesheet" type="text/css" />
    <link href="style.css" rel="stylesheet" type="text/css" />
    <script src="js/mui.min.js"></script>
    <title>PoweRss</title>
  </head>
  <body>
    <div id="sidebar">
      <div class="mui--text-white mui--text-display1 mui--align-vertical">PoweRss</div>
    </div>
    <div id="content" class="mui-container-fluid">

<table class="mui-table mui-table--bordered">
  <thead>
    <tr>
      <th>Item</th>
      <th>Site</th>
      <th>Time</th>
    </tr>
    </thead>
    <tbody>
<?php

require('config.php');

$sql = 'SELECT id,data,lastupdate FROM items ORDER BY lastupdate DESC LIMIT 10';
foreach($db->query($sql,PDO::FETCH_ASSOC) as $row){
  $data = json_decode($row['data']);
  // TODO: Assumes RSS/RDF spec
  $linkparts = parse_url($data->link);
?>
          <tr>
            <td>
              <a href="<?php echo $data->link; ?>" target="_blank">
                <?php echo $data->title; ?> 
              </a>
            </td>
            <td>
              <small>[<?php echo $linkparts['host']; ?>]</small>
            </td>
            <td>
              <?php echo date('r',$row['lastupdate']); ?>
            </td>
          </tr>

<?php
}

?>
        </tbody>
      </table>

    </div>
  </body>
</html>
