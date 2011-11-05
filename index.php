<html>
<head>
<title>Investd</title>
</head>
<body>
<div class="main">
<?php
error_reporting(0);
if (isset($_POST['searchterm'])) {
    $text = <<<TEXT
<h1>Your Results</h1>
<p>Your search results are below. Select all the MPs you'd like to send the message to, write your message, and click send. You can click on any of the MPs names to see their full biography.</p>
<form action="index.php" method="post">
<hr />
TEXT;
    echo($text.PHP_EOL);
    if (strpos($_POST['searchterm']," ") !== False) {
        $searchterm = '"'.$_POST['searchterm'].'"';
    } else {
        $searchterm = $_POST['searchterm'];
    }
    $searchxml = new XMLReader;
    if ($_POST['topic'] != "none") {
        $url = 'http://data.parliament.uk/EDMi/EDMi.svc/List?take='.$_POST['take'].'&topic='.$_POST['topic'].'&EdmContains='.$_POST['searchterm'];
    } else {
        $url = 'http://data.parliament.uk/EDMi/EDMi.svc/List?take='.$_POST['take'].'&EdmContains='.$searchterm;
    }
    $searchxml->open($url);
    $result = 1;
    $count = 1;
    $lastedmid = null;
    while ($searchxml->read()) {
        if (($searchxml->name === "ee:Edm") && ($searchxml->getAttribute('id') !== $lastedmid)) {
            echo("<div class=\"result\" id=\"result".$result."\">".PHP_EOL);
            $id = $searchxml->getAttribute('id');
            $domnode = $searchxml->expand();
            echo("<h2>".$domnode->childNodes->item(0)->textContent."</h2>".PHP_EOL);
            echo("<p>".$domnode->childNodes->item(1)->textContent."</p>".PHP_EOL);
            echo("<div class=\"mplist\">".PHP_EOL);
            echo("<ul>".PHP_EOL);
            $url = "http://data.parliament.uk/EDMi/EDMi.svc/".$id."/Signature/List";
            $edmxml = new XMLReader();
            $edmxml->open($url);
            $lastid = null;
            $firsttag2 = True;
            while ($edmxml->read()) {
                if (($edmxml->name === 'es:SignedMember') && ($edmxml->getAttribute('id') != $lastid)) {
                    $searchname = str_replace(", ","-",strtolower($edmxml->readString()));
                    $parlyxml = new XMLReader();
                    $parlyxml->open("http://data.parliament.uk/resources/members/api/commons/".$searchname);
                    $first = True;
                    while ($parlyxml->read()) {
                        if (($parlyxml->name === "m:commonsMember") && ($first)) {
                            $first = False;
                            $count++;
                            echo("<li><input type=\"checkbox\" name=\"mp".$count."\" value=\"".$parlyxml->getAttribute('m:id')."\"<a href=\"http://www.parliament.uk/biographies/".$searchname."/".$parlyxml->getAttribute('m:dodsId')."\">".$edmxml->readString()."</a></li>".PHP_EOL);
                        }
                    }
                    $lastid = $edmxml->getAttribute('id');
                }
            }
            echo("</ul>".PHP_EOL."</div>".PHP_EOL."</div>".PHP_EOL);
            $result++;
            $lastedmid = $id;
        }
    }
} elseif (isset($_POST['message'])) {
} else {
?>
<div class="search">
<form action="index.php" method="post" >
<p>Search Term: <input type="text" name="searchterm" /><p>
<p>How many records: <input type="text" name="take" value="10" /><p>
<p>Topic Area (Optional): <select name="topic">
<option value="none">Any</option>
<?php
$url = "http://data.parliament.uk/EDMi/EDMi.svc/Topic/List";
$xml = new XMLReader();
$xml->open($url);
$lastid = null;
while ($xml->read()) {
    if (($xml->name === 'Topic') && ($xml->getAttribute('id') != $lastid)) {
        echo("<option value=\"".$xml->getAttribute('id')."\">".$xml->readString()."</option>".PHP_EOL);
        $lastid = $xml->getAttribute('id');
    }
}
?>
</select></p>
<p><input type="submit" /><input type="reset" /></p>
<?php
}
?>
</div>
</body>
</html>
