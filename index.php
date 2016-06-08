<?php

require('config.php');

$feedid = (int)@$_GET['feedid'];

$hide = (int)@$_GET['hide'];
if( $hide > 0){
  $hidesql = 'UPDATE items SET hidden=1 WHERE id=:id';
  $hideq = $db->prepare($hidesql);
  $hideq->execute(
    array(
      ':id' => $hide,
    )
  );
}

$hideall = isset($_GET['hideall']);
if($hideall){
  $hidesql = 'UPDATE items SET hidden=1 WHERE 1';
  if($feedid > 0){
    $hidesql .= " AND feedid = $feedid";
  }
  $hideq = $db->prepare($hidesql);
  $hideq->execute(
    array(
      ':id' => $hide,
      ':feedid' => $feedid,
    )
  );
}

?><!doctype html>
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
      <ul>
        <a href="?">All</a>
      </ul>
    </div>
    <div id="content" class="mui-container-fluid">

<?php

$itemcounts = "SELECT count(*) FROM items WHERE hidden = 0";
if($feedid > 0){
  $itemcounts .= " AND feedid = $feedid";
}
$itemcountq = $db->prepare($itemcounts);
$itemcountq->execute();
$itemcount = $itemcountq->fetchColumn(0);

$hiddenitemcounts = "SELECT count(*) FROM items WHERE 1";
if($feedid > 0){
  $hiddenitemcounts .= " AND feedid = $feedid";
}
$hiddenitemcountq = $db->prepare($hiddenitemcounts);
$hiddenitemcountq->execute();
$hiddenitemcount = $hiddenitemcountq->fetchColumn(0);

?>

      <h1>Items</h1>
      <p><?php echo $itemcount; ?>/<?php echo $hiddenitemcount; ?></p>

      <a href="?hideall&feedid=<?php echo $feedid; ?>" class="mui-btn mui-btn--small mui-btn--primary">Hide All</a>

      <table class="mui-table mui-table--bordered">
        <thead>
          <tr>
            <th></th>
            <th>Item</th>
            <th>Site</th>
            <th>Time</th>
          </tr>
          </thead>
          <tbody>
<?php

$page = (int)@$_GET['page'];
$offset = $page*ITEMS_PER_PAGE;

if($feedid > 0){
  $feeditems = " AND feedid = $feedid";
} else {
  $feeditems = "";
}
$items = "SELECT id,data,lastupdate FROM items WHERE hidden = 0 $feeditems ORDER BY lastupdate DESC LIMIT ".ITEMS_PER_PAGE.' OFFSET '.$offset;
foreach($db->query($items,PDO::FETCH_ASSOC) as $row){
  $data = json_decode($row['data']);
  // TODO: Assumes RSS/RDF spec
  $linkparts = parse_url($data->link);
  $title = is_string($data->title) ? $data->title : "[Error Loading Title]";
?>
            <tr>
              <td>
                <a href="?feedid=<?php echo $feedid;?>&hide=<?php echo $row['id']; ?>" class="mui-btn mui-btn--small mui-btn--primary">hide</a>
              </td>
              <td>
                <a href="<?php echo $data->link; ?>" target="_blank">
                  <?php echo $title; ?>
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
<?php

$pagstart = (int) max($page-PAG_PER_PAGE/2,1);
$pagend = (int) min($pagstart + PAG_PER_PAGE,ceil($itemcount/ITEMS_PER_PAGE));

?>
      <a href="?feedid=<?php echo $feedid; ?>" class="mui-btn mui-btn--small mui-btn--primary">&lt;&lt;</a>
<?php
for($i=$pagstart;$i<$pagend;$i++){
  $accent = "";
  if($i == $page){
    $accent = "mui-btn--accent";
  }
?>
      <a href="?page=<?php echo $i; ?>" class="mui-btn mui-btn--small mui-btn--primary <?php echo $accent; ?>">
        <?php echo $i; ?>
      </a>
<?php
}
?>
      <h1>Unread Feeds</h1>
      <table class="mui-table mui-table--bordered">
        <thead>
          <tr>
            <th>Items</th>
            <th>URL</th>
            <th>Last Update</th>
          </tr>
        </thead>
        <tbody>
<?php

$feeds = '
SELECT feeds.id,feeds.url,feeds.lastupdate,COUNT(*) as itemcount
FROM items
LEFT JOIN feeds on feeds.id = items.feedid
WHERE items.hidden = 0
GROUP BY feeds.id,feeds.url,feeds.lastupdate ORDER BY itemcount DESC';
foreach($db->query($feeds,PDO::FETCH_ASSOC) as $row){
?>
        <tr>
          <td><?php echo $row['itemcount']; ?></td>
          <td><a href="?feedid=<?php echo $row['id']; ?>"><?php echo $row['url']; ?></td>
          <td><?php echo date('r',$row['lastupdate']); ?></td>
        </tr>
<?php
}
?>
        </tbody>
      </table>
    </div>
  </body>
</html>
