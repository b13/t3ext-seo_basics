<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "seo_basics".
 *
 * Auto generated 22-11-2012 11:58
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Basic SEO Features',
	'description' => 'Introduces a separate field for the title-tag per page, easy and SEO-friendly keywords and description editing in a new module as well as a Google Sitemap (XML) and a clean output in the HTML Source code.',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.8.3',
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
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'realurl' => '0.0.0-0.0.0',
			'' => '',
		),
		'conflicts' => '',
		'suggests' => 
		array (
		),
	),
);

?>