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
echo('<p>Topics List has been updated. <a href="index.php">Go Home?</a></p>');
?>
</div>
<div class="footer center">
<hr />
<p>This system caches MP and Topic data to reduce running time, but does not automatically update them. If you would like to update the data, <a href="refreshmps.php">click here to refresh MPs data</a> or <a href="refreshtopics.php">click here to refresh topic data</a>. Be warned that this may take up to 2 minutes to complete</p>
<p>Samuel Littley for RSParly</p>
</body>
</html>
