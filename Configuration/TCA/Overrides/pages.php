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
	),
    'tx_seo_robots' => array(
        'exclude' => 1,
        'label'   => 'LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.tx_seo_robots',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectSingle',
            'minitems' => 1,
            'maxitems' => 1,
            'size' => 1,
            'items' => array(
                array('LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.tx_seo_robots.I.0', '0'),
                array('LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.tx_seo_robots.I.1', '1'),
                array('LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.tx_seo_robots.I.2', '2'),
                array('LLL:EXT:seo_basics/Resources/Private/Language/db.xml:pages.tx_seo_robots.I.3', '3'),
            ),
        )
    ),
 );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_seo_titletag, tx_seo_canonicaltag, tx_seo_robots', 1, 'before:keywords');
