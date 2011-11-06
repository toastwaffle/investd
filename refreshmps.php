<html>
<head>
<title>Investd</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div class="main">
<h1>Investd</h1>
<p>Welcome to Investd. This is a service which lets you find and email MPs who, due to their interests and previous parliament history, may actually be invested enough in what you say to act on it.</p>
<p>The system works through searching Early Day Motions (EDMs). These are the few small things that MPs write when they care about something, and want to get it sorted. They then get all their MP friends to sign the EDM, and eventually, it may be debated in the House of Commons</p>
<p>You can enter a search term, and optionally choose an EDM topic area. You will be given a list of all the matching EDMs, with tickboxes for every MP who signed it. Tick the boxes, enter your message, and click submit.</p>
<hr />
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
echo('<p>MPs List has been updated. <a href="index.php">Go Home?</a></p>');
?>
</div>
<div class="footer center">
<hr />
<p>This system caches MP and Topic data to reduce running time, but does not automatically update them. If you would like to update the data, <a href="refreshmps.php">click here to refresh MPs data</a> or <a href="refreshtopics.php">click here to refresh topic data</a>. Be warned that this may take up to 2 minutes to complete</p>
<p>Samuel Littley for RSParly</p>
</body>
</html>
