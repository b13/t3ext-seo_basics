<?php

########################################################################
# Extension Manager/Repository config file for ext "seo_basics".
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Basic SEO Features',
	'description' => 'Adds a separate field for the title-tag per page, easy and SEO-friendly keywords and description editing in a new module as well as a flexible Google Sitemap (XML).',
	'category' => 'fe',
	'version' => '0.10.1',
	'state' => 'stable',
	'modify_tables' => 'pages,pages_language_overlay',
	'author' => 'Benni Mack',
	'author_email' => 'benni@typo3.org',
	'constraints' => array(
		'depends' => array(
			'typo3' => '8.7.0-8.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'realurl' => '0.0.0-0.0.0',
			'cooluri' => '0.0.0-0.0.0',
		),
	),
	'suggests' => array(
	),
);
