<?php

require_once 'DiscogsDb.php';

// $file = "labels_20080309.xml";
$file = "discogs_20170101_labels.xml";

$currElem = '';
$currLabel;
$currCount = [];
$elementCount = [];

function StartElement($parser, $name, $attrs) {
    global $currElem;
    $currElem = strtolower($name);
}

function CharacterData($parser, $data) {
    global $currElem, $currLabel;
    global $currCount;

    if ($currElem == 'id') {
        if (isset($currLabel['id'])) {
            NewLabel();
            $currLabel = [];
        }
        $currLabel['id'] = $data;
    } else {
        if (! isset($currCount[$currElem])) {
            $currCount[$currElem] = 0;
        }
        $currCount[$currElem]++;
        $currLabel[$currElem] = $data;
    }
}

function NewLabel() {
    global $db, $currLabel;
    global $currCount, $elementCount;

    foreach ($currCount as $name => $count) {
        if (! isset($elementCount[$name]) || $elementCount[$name] < $count) {
            $elementCount[$name] = $count;
        }
    }
    $currCount = [];
    // $db->addLabel($currLabel);
    // echo $currLabel['id'] . " " . $currLabel['name'] . "<br>";
    // echo $currLabel['labels'] . "<br>";
    // echo $currLabel['contactinfo'] . "<br>";
    // echo $currLabel['profile'] . "<br>";
    // echo $currLabel['data_quality'] . "<br>";
    // echo $currLabel['url'] . "<br>";
    // echo $currLabel['label'] . "<br>";
    // echo $currLabel['parentlabel'] . "<br>";
}

$xml_parser = xml_parser_create();
xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
xml_set_element_handler($xml_parser, "startElement", "");
xml_set_character_data_handler($xml_parser, "characterData");
if (!($fp = fopen($file, "r"))) {
    die("could not open XML input");
}

###################################################################

$db = new DiscogsDb();

$i = 0;
while ($data = fread($fp, 4096)) {
    if (!xml_parse($xml_parser, $data, feof($fp))) {
        echo "<br><br>";
        printf("XML error: %s at line %d",
            xml_error_string(xml_get_error_code($xml_parser)),
            xml_get_current_line_number($xml_parser));
        break;
    }
    // if ($i++ > 10) {
    //     break;
    // }
}
NewLabel(); // last label not processed in loop

xml_parser_free($xml_parser);

// echo join(", ", array_keys($elementCount)) . "<br>";

foreach ($elementCount as $name => $count) {
    echo "$count $name<br>";
}

?>
