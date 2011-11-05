<?php
header("Content-type: application/xml");
$url = "http://data.parliament.uk/EDMi/EDMi.svc/Topic/List";
$oldxml = new XMLReader();
$oldxml->open($url);
$writer = new XMLWriter;
$writer->openURI('php://output');
$writer->startDocument('1.0', 'UTF-8');

$writer->startElement('topics');
    $lastid = null;
    while ($oldxml->read()) {
        if (($oldxml->name === 'Topic') && ($oldxml->getAttribute('id') != $lastid)) {
            $writer->startElement('topic');
                $writer->startAttribute('id');
                    $writer->text($oldxml->getAttribute('id'));
                $writer->endAttribute();
                $writer->text($oldxml->readString());
            $writer->endElement();
            $lastid = $oldxml->getAttribute('id');
        }
    }
$writer->endElement();
$writer->endDocument();
?>
