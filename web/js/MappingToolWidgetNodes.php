<?php

require_once '../include.php';

// support browser caching for this file
// by checking the dates of the given files
// to change the headers accordingly to updates
Tht_Helper_Header::cacheJS(
    array(
        __ROOT__ . '/lib/vendor/Tht/Dml/ExtJavaScriptClasses.php',
        __ROOT__ . '/grammar/dbpedia_mapping_grammar.xml'
    )
);

Tht_Dml_ExtJavaScriptClasses::writeJs(__ROOT__ . '/grammar/dbpedia_mapping_grammar.xml');