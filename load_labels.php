<?php

// require_once 'DiscogsDb.php';

// $file = "labels_20080309.xml";
$file = "../discogs_20170101_labels.xml";
$br = "\n";
// $br = "<br>";

$currElem = '';
$currLabel = [];

function StartElement($parser, $name, $attrs) {
    global $currElem;
    $currElem = strtolower($name);
}

function CharacterData($parser, $data) {
    global $currElem, $currLabel;

    $data = trim($data);

    if ($currElem == 'id') {
        if (isset($currLabel['id'])) {
            NewLabel();
            $currLabel = [];
        }
        $currLabel['id'] = $data;
    } else {
        if (isset($currLabel[$currElem])) {
            $currLabel[$currElem] .= $data;
        } else {
            $currLabel[$currElem] = $data;
        }
        // echo "$currElem $currLabel[$currElem]$br";
    }
}

// $db = new DiscogsDb();

function NewLabel() {
    global $db, $currLabel;
    $db->addLabel($currLabel);
    InsertLabel($currLabel);
    // PrintLabel($currLabel);
}

function PrintLabel($label) {
    global $br;
    echo $label['id'] . " " . $label['name'] . $br;
    PrintVal('labels');
    PrintVal('contactinfo');
    PrintVal('profile');
    PrintVal('data_quality');
    PrintVal('url');
    PrintVal('label');
    PrintVal('parentlabel');
    echo $br;
}

function InsertLabel($label) {
    global $br;
    $sql = "insert into labels (id, labels, contactinfo, profile, data_quality, url, label, parentlabel) values(:id, :labels, :contactinfo, :profile, :data_quality, :url, :label, :parentlabel)";
    $db = DbConnect();
    $statement = $db->prepare($sql);
    $statement->bindParam(':id', $label['id']);
    $statement->bindParam(':labels', $label['labels']);
    $statement->bindParam(':contactinfo', $label['contactinfo']);
    $statement->bindParam(':profile', $label['profile']);
    $statement->bindParam(':data_quality', $label['data_quality']);
    $statement->bindParam(':url', $label['url']);
    $statement->bindParam(':label', $label['label']);
    $statement->bindParam(':parentlabel', $label['parentlabel']);
    $statement->execute();
}

function DbConnect() {
    $dbserver = "localhost";
    $dbname = "discogsdb";
    $dbuser = "discogsdb";
    $dbpass = "discogsdb";

    $db = new PDO("mysql:host=$dbserver;dbname=$dbname", $dbuser, $dbpass);

    return $db;
}

$keyIndex = [];

function PrintVal($key) {
    global $currLabel, $br, $keyIndex;
    $keyIndex[$key] = 1;
    if (isset($currLabel[$key]) && $currLabel[$key]) {
        echo "$key = '" . $currLabel[$key] . "'$br";
    }
}

$xml_parser = xml_parser_create();
xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
xml_set_element_handler($xml_parser, "startElement", "");
xml_set_character_data_handler($xml_parser, "characterData");
if (!($fp = fopen($file, "r"))) {
    die("could not open XML input");
}

###################################################################
# Go!

$i = 0;
while ($data = fread($fp, 4096)) {
    if (!xml_parse($xml_parser, $data, feof($fp))) {
        echo "$br$br";
        printf("XML error: %s at line %d",
            xml_error_string(xml_get_error_code($xml_parser)),
            xml_get_current_line_number($xml_parser));
        break;
    }
    if ($i++ > 10) {
        break;
    }
}
NewLabel(); // last label not processed in loop

###################################################################

xml_parser_free($xml_parser);

// echo join($br, array_keys($keyIndex));


?>
