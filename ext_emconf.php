<?php

########################################################################
# Extension Manager/Repository config file for ext "seo_basics".
#
# Auto generated 05-07-2011 20:28
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Basic SEO Features',
	'description' => 'Introduces a separate field for the title-tag per page, easy and SEO-friendly keywords and description editing in a new module as well as a Google Sitemap (XML) and a clean output in the HTML Source code.',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.8.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'pages,pages_language_overlay',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Benjamin Mack',
	'author_email' => 'benni@typo3.org',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'realurl' => '0.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:17:{s:9:"ChangeLog";s:4:"4552";s:22:"class.tx_seobasics.php";s:4:"765c";s:30:"class.tx_seobasics_sitemap.php";s:4:"1ce3";s:16:"ext_autoload.php";s:4:"d1ba";s:21:"ext_conf_template.txt";s:4:"238d";s:12:"ext_icon.gif";s:4:"0858";s:17:"ext_localconf.php";s:4:"db8c";s:14:"ext_tables.php";s:4:"c52f";s:14:"ext_tables.sql";s:4:"e0ac";s:33:"Resources/Private/Language/db.xml";s:4:"0eda";s:14:"doc/manual.sxw";s:4:"fa8b";s:40:"modfunc1/class.tx_seobasics_modfunc1.php";s:4:"d035";s:22:"modfunc1/locallang.php";s:4:"2514";s:29:"modfunc1/js/mootools.v1.11.js";s:4:"4129";s:24:"modfunc1/js/seobasics.js";s:4:"1986";s:20:"static/constants.txt";s:4:"f21e";s:16:"static/setup.txt";s:4:"9902";}',
	'suggests' => array(
	),
);

?>
