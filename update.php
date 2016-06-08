<?php
require('config.php');

$feeds = 'SELECT url,lastupdate FROM feeds';
$data = array();
// TODO: add timeout
$feeds_updated = 0;
$items_added = 0;
foreach($db->query($feeds,PDO::FETCH_ASSOC) as $row){
  if($row['lastupdate'] + UPDATE_INTERVAL < time()){
    $feeds_updated++;

    // TODO: Asssumes input is a url
    // TODO: Assumes return is xml
    $rss = simplexml_load_file($row['url']);

    // TODO: Assumes that RSS/RDF will render correctly for everything
    if($rss->getName() !== "rss" and $rss->getName() !== "RDF"){
      echo 'Cannot parse feed of type '.$rss->getName().' `'.$row['url']."`\n";
    } else {
      // TODO: Assumes RSS/RDF has channel->item
      foreach($rss->channel->item as $item) {
        // TODO: Assumes item is an object
        $json_item = json_encode($item);
        $hash = md5($json_item);
        $check = "SELECT EXISTS(SELECT 1 FROM items WHERE hash='$hash')";
        $checkq = $db->query($check);
        $checkr = $checkq->fetchAll();

        if($checkr[0][0] == 0){
          $items_added++;
          $insert = $db->prepare('INSERT INTO items (`lastupdate`,`hash`,`data`) VALUES (:lastupdate,:hash,:data)');
          // TODO: Assumes unix time stamp is returned from strtotime
          $t = strtotime($item->pubDate);
          if($t === false){ // fallback for folks who don't have pubDate, we just shove now in.
            $t = time();
          }
          $insert->execute(
            array(
              ':lastupdate' => $t,
              ':hash' => $hash,
              ':data' => $json_item,
            )
          );
        }

      }

    }

    $newlastupdate = 'UPDATE feeds SET lastupdate = '.time();
    $db->query($newlastupdate);

  }
}
echo "Updated $feeds_updated with $items_added new items [completed @ ".date('r',time())."]\n";
