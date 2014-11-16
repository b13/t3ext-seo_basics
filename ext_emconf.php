<?php

########################################################################
# Extension Manager/Repository config file for ext "seo_basics".
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Basic SEO Features',
	'description' => 'Adds a separate field for the title-tag per page, easy and SEO-friendly keywords and description editing in a new module as well as a flexible Google Sitemap (XML).',
	'category' => 'fe',
	'version' => '0.9.0-dev',
	'state' => 'stable',
	'modify_tables' => 'pages,pages_language_overlay',
	'clearcacheonload' => 1,
	'author' => 'Benni Mack',
	'author_email' => 'benni@typo3.org',
	'author_company' => '',
	'constraints' => array(
		'depends' => array(
			'realurl' => '0.0.0-0.0.0',
			'typo3' => '6.2.0-7.9.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);
