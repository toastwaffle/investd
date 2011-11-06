<?php
$fh = fopen("topics.txt","w");
$url = "http://data.parliament.uk/EDMi/EDMi.svc/Topic/List";
$xml = new XMLReader();
$xml->open($url);
$lastid = null;
while ($xml->read()) {
    if (($xml->name === 'Topic') && ($xml->getAttribute('id') != $lastid)) {
        fwrite($fh,"<option value=\"".$xml->getAttribute('id')."\">".$xml->readString()."</option>".PHP_EOL);
        $lastid = $xml->getAttribute('id');
    }
}
fclose($fh);
?>
