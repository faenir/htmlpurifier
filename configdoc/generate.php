<?php

/**
 * Generates XML and HTML documents describing configuration.
 * @note PHP 5.2+ only!
 */

/*
TODO:
- make XML format richer
- extend XSLT transformation (see the corresponding XSLT file)
- allow generation of packaged docs that can be easily moved
- multipage documentation
- determine how to multilingualize
- add blurbs to ToC
*/

if (version_compare(PHP_VERSION, '5.2.0', '<')) exit('PHP 5.2.0 or greater required.');
error_reporting(E_ALL | E_STRICT);

chdir(dirname(__FILE__));

// load dual-libraries
require_once '../extras/HTMLPurifierExtras.auto.php';
require_once '../library/HTMLPurifier.auto.php';

// setup HTML Purifier singleton
HTMLPurifier::getInstance(array(
    'AutoFormat.PurifierLinkify' => true
));

$interchange = HTMLPurifier_ConfigSchema_InterchangeBuilder::buildFromDirectory();
$interchange->validate();

$style = 'plain'; // use $_GET in the future, careful to validate!
$configdoc_xml = 'configdoc.xml';

$xml_builder = new HTMLPurifier_ConfigSchema_Builder_Xml();
$xml_builder->openURI($configdoc_xml);
$xml_builder->build($interchange);

$xslt = new ConfigDoc_HTMLXSLTProcessor();
$xslt->importStylesheet(dirname(__FILE__) . "/styles/$style.xsl");
$output = $xslt->transformToHTML($configdoc_xml);

if (!$output) {
    echo "Error in generating files\n";
    exit(1);
}

// write out
file_put_contents("$style.html", $output);

if (php_sapi_name() != 'cli') {
    // output (instant feedback if it's a browser)
    echo $output;
} else {
    echo 'Files generated successfully.';
}

