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
function sendemail($to,$subject,$message,$headers) {
    // mail($to,$subject,$message,$headers)
    echo("<p>".htmlentities("mail($to,$subject,".substr($message,0,50)."...,$headers)")."</p>".PHP_EOL);
}
if (isset($_POST['searchterm'])) {
    if (!file_exists("MPs.json")) {
        include("refreshmps.php");
    }
    $fh = fopen("MPs.json","r");
    $json = fread($fh,filesize("MPs.json"));
    $MPs = json_decode($json,True);
    fclose($fh);
    $text = <<<TEXT
<h1>Your Results</h1>
<p class="small right"><a href="index.php">New Search</a></p>
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
                    if (isset($MPs[$edmxml->getAttribute('id')])) {
                        echo("<li><input type=\"checkbox\" name=\"mp".$count."\" value=\"".$edmxml->getAttribute('id')."\"<a href=\"http://www.parliament.uk/biographies/".$MPs[$edmxml->getAttribute('id')]['name']."/".$MPs[$edmxml->getAttribute('id')]['dodsid']."\">".$edmxml->readString()."</a></li>".PHP_EOL);
                        $count++;
                    }
                    $lastid = $edmxml->getAttribute('id');
                }
            }
            echo("<div class=\"clear\"></div></ul><hr />".PHP_EOL."</div>".PHP_EOL."</div>".PHP_EOL);
            $result++;
            $lastedmid = $id;
        }
    }
    echo('<input type="hidden" name="mpscount" value="'.$count.'" />');
?>
<br />
<p>Write your message below. All fields must be filled in</p>
<br />
<p>Your Name: <input type="text" name="name" /></p>
<p>Your Email: <input type="email" name="email" /></p>
<p>Message Subject: <input type="text" name="subject" /></p>
<p>Message Body:</p><textarea rows="20" cols="70" name="message"></textarea>
<p><input type="submit" /><input type="reset" /></p>
</form>
<?php
} elseif (isset($_POST['message'])) {
    if (!file_exists("MPs.json")) {
        include("refreshmps.php");
    }
    $fh = fopen("MPs.json","r");
    $json = fread($fh,filesize("MPs.json"));
    $MPs = json_decode($json,True);
    fclose($fh);
    for ($i = 1; $i <= $_POST['mpscount']; $i++) {
        if (isset($_POST['mp'.$i])) {
            sendemail($MPs[$_POST['mp'.$i]]['email'],$_POST['subject'],$_POST['message'],"From: ".$_POST['name']." <".$_POST['email'].">");
        }
    }
} else {
?>
<div class="search">
<form action="index.php" method="post" >
<p>Search Term: <input type="text" name="searchterm" /><p>
<p>How many records: <input type="text" name="take" value="10" /><p>
<p>Topic Area (Optional): <select name="topic">
<option value="none">Any</option>
<?php
if (!file_exists("topics.txt")) {
    include("refreshtopics.php");
}
$fh = fopen("topics.txt","r");
while ($text = fread($fh,1024)) {
    echo($text);
}
fclose($fh);
?>
</select></p>
<p><input type="submit" /><input type="reset" /></p>
<?php
}
?>
</div>
<div class="footer center">
<hr />
<p>This system caches MP and Topic data to reduce running time, but does not automatically update them. If you would like to update the data, <a href="refreshmps.php">click here to refresh MPs data</a> or <a href="refreshtopics.php">click here to refresh topic data</a>. Be warned that this may take up to 2 minutes to complete</p>
<p>Samuel Littley for RSParly</p>
</body>
</html>
