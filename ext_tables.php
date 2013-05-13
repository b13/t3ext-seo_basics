<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


	// Adding Web>Info module for SEO management
if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_seobasics_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY) . 'modfunc1/class.tx_seobasics_modfunc1.php',
		'LLL:EXT:seo_basics/Resources/Private/Language/db.xml:moduleFunction.tx_seobasics_modfunc1',
		'function',
		'online'
	);
}



	// Adding title tag field to pages TCA
$tmpCol = array(
	'tx_seo_titletag' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.titletag',
		'config' => Array (
			'type' => 'input',
			'size' => '70',
			'max' => '70',
			'eval' => 'trim'
		)
	),
	'tx_seo_canonicaltag' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.canonicaltag',
		'config' => Array (
			'type' => 'input',
			'size' => '70',
			'max' => '70',
			'eval' => 'trim'
		)
	)
);
t3lib_extMgm::addTCAcolumns('pages', $tmpCol, 1);
t3lib_extMgm::addTCAcolumns('pages_language_overlay', $tmpCol, 1);

t3lib_extMgm::addToAllTCAtypes('pages', 'tx_seo_titletag;;;;, tx_seo_canonicaltag', 1, 'before:keywords');
t3lib_extMgm::addToAllTCAtypes('pages_language_overlay', 'tx_seo_titletag, tx_seo_canonicaltag, nav_title, tx_realurl_pathsegment;;;;', "4,5", 'after:subtitle');

$TCA['pages_language_overlay']['interface']['showRecordFieldList'] .= ',tx_seo_titletag, tx_seo_canonicaltag';


	// Adding a static template TypoScript configuration from static/
t3lib_extMgm::addStaticFile($_EXTKEY, 'static', 'Metatags and XML Sitemap');

