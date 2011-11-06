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
    $email = null;
    $id = null;
    $dodsid = null;
    $searchname = null;
    $namearr = explode(" ",strtolower($item->nodeValue));
    $searchname = str_replace(",","",array_pop($namearr)."-".$namearr[0]);
    $parlyxml = new XMLReader();
    $parlyxml->open("http://data.parliament.uk/resources/members/api/commons/".$searchname);
    while ($parlyxml->read()) {
        if ($parlyxml->name === "m:commonsMember") {
            $dodsid = $parlyxml->getAttribute('m:dodsId');
            $id = $parlyxml->getAttribute('m:id');
            break;
        }
    }
    $parlyxml->close();
    $url = "http://data.parliament.uk/resources/members/api/biography/".$id;
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
    curl_setopt($ch,CURLOPT_HEADER,False);
    $xml = curl_exec($ch);
    curl_close($ch);
    $bioxml = new DOMDocument();
    $bioxml->loadXML($xml);
    $bioitems = $bioxml->getElementsByTagNameNS("http://www.parliament.uk/xmlns/metadata/member/2011/01/biography/","email");
    foreach ($bioitems as $bioitem) {
        if (filter_var($bioitem->nodeValue,FILTER_VALIDATE_EMAIL)) {
            $email = $bioitem->nodeValue;
        }
    }
    if ($email != "") {
            $members[$item->attributes->item(0)->nodeValue] = array('name' => $searchname, 'dodsid' => $dodsid, 'id' => $id, 'email' => $email);
    }
}
fwrite($fp,json_encode($members));
fclose($fp);
?>
