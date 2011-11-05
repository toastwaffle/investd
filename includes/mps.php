<?php
error_reporting(0);
if (isset($_GET['id'])) {
echo("<ul>".PHP_EOL);
$url = "http://data.parliament.uk/EDMi/EDMi.svc/".$_GET['id']."/Signature/List";
$edmxml = new XMLReader();
$edmxml->open($url);
    $lastid = null;
    while ($edmxml->read()) {
        if (($edmxml->name === 'es:SignedMember') && ($edmxml->getAttribute('id') != $lastid)) {
            $searchname = str_replace(", ","-",strtolower($edmxml->readString()));
            $parlyxml = new XMLReader();
            $parlyxml->open("http://data.parliament.uk/resources/members/api/commons/".$searchname);
            $first = True;
            while ($parlyxml->read()) {
                if (($parlyxml->name === "m:commonsMember") && ($first)) {
                    $first = False;
                    echo("<li><a href=\"http://www.parliament.uk/biographies/".$searchname."/".$parlyxml->getAttribute('m:dodsId')."\">".$edmxml->readString()."</a></li>".PHP_EOL);
                }
            }
            $lastid = $edmxml->getAttribute('id');
        }
    }
}
?>
