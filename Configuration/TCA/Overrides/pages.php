<?php
defined('TYPO3_MODE') or die();

	// Adding title tag field to pages TCA
$additionalColumns = array(
	'tx_seo_titletag' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.titletag',
		'config' => array(
			'type' => 'input',
			'size' => 70,
			'eval' => 'trim'
		)
	),
	'tx_seo_canonicaltag' => array(
		'exclude' => 1,
		'label'   => 'LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.canonicaltag',
		'config'  => array(
			'type' => 'input',
			'size' => 70,
			'eval' => 'trim'
		)
	)
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_seo_titletag, tx_seo_canonicaltag', 1, 'before:keywords');
