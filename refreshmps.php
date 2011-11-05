<?php
error_reporting(E_ALL);
$fp = fopen('MPs.json','w');
$members = array();
$url = "http://data.parliament.uk/EDMi/EDMi.svc/Member/List";
$ch = curl_init($url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
curl_setopt($ch,CURLOPT_HEADER,False);
$xml = curl_exec($ch);
curl_close($ch);
$edmxml = new DOMDocument();
$edmxml->loadXML($xml);
$items = $edmxml->getElementsByTagName('PickListItem');
foreach ($items as $item) {
    $namearr = explode(" ",strtolower($item->nodeValue));
    $searchname = str_replace(",","",array_pop($namearr)."-".$namearr[0]);
    $parlyxml = new XMLReader();
    $parlyxml->open("http://data.parliament.uk/resources/members/api/commons/".$searchname);
    while ($parlyxml->read()) {
        if ($parlyxml->name === "m:commonsMember") {
            $first = False;
            $members[$item->attributes->item(0)->nodeValue] = array('name' => $searchname, 'dodsid' => $parlyxml->getAttribute('m:dodsId'));
            break;
        }
    }
    $parlyxml->close();
}
fwrite($fp,json_encode($members));
fclose($fp);
?>
