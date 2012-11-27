<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007-2011 Benjamin Mack <benni@typo3.org>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/** 
 * @author	Benjamin Mack (benni@typo3.org) 
 * @subpackage	tx_seobasics
 * 
 * This package includes all functions for generating XML sitemaps
 */

require_once(PATH_t3lib.'class.t3lib_pagetree.php');

class tx_seobasics_sitemap {
	protected $conf;


	/**
	 * Generates a XML sitemap from the page structure
	 *
	 * @param       string	the content to be filled, usually empty
	 * @param       array	additional configuration parameters
	 * @return      string	the XML sitemap ready to render
	 */
	public function renderXMLSitemap($content, $conf) {
		$this->conf = $conf;
		$id = intval($GLOBALS['TSFE']->id);
		$depth = 50;
		$additionalFields = 'uid,pid,doktype,shortcut,crdate,SYS_LASTCHANGED';


		// precedence: $conf['useDomain'], config.baseURL, the domain record, config.absRefPrefix
		if (isset($conf['useDomain'])) {
			if ($conf['useDomain'] == 'current') {
				$baseURL = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
			} else {
				$baseURL = $conf['useDomain'];
			}
		}
		
		if (!$baseURL) {
			$baseURL = $GLOBALS['TSFE']->baseUrl;
		}

		if (!$baseURL) {
			$domainPid = $GLOBALS['TSFE']->findDomainRecord();
			if ($domainPid) {
				$domainRecords = $GLOBALS['TSFE']->sys_page->getRecordsByField('sys_domain', 'pid', $domainPid, ' AND redirectTo = ""', '', 'sorting ASC', '1');
				if (count($domainRecords)) {
					$domainRecord = reset($domainRecords);
					$baseURL = $domainRecord['domainName'];
				}
			}
		}

		if ($baseURL && strpos($baseURL, '://') === FALSE) {
			$baseURL = 'http://' . $baseURL;
		}

		if (!$baseURL && $GLOBALS['TSFE']->absRefPrefix) {
			$baseURL = $GLOBALS['TSFE']->absRefPrefix;
		}
		if (!$baseURL) {
			die('Please add a domain record at the root of your TYPO3 site in your TYPO3 backend.');
		}

		// add appending slash
		$baseURL = rtrim($baseURL, '/') . '/';

			// -- do a 301 redirect to the "main" sitemap.xml if not already there
		if ($this->conf['redirectToMainSitemap'] && $baseURL) {
			$sitemapURL = $baseURL . 'sitemap.xml';
			$requestURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
			if ($requestURL != $sitemapURL && strpos($requestURL, 'sitemap.xml')) {
				header('Location: ' . t3lib_div::locationHeaderUrl($sitemapURL), true, 301);
			}
		}

			// Initializing the tree object
		$treeStartingRecord = $GLOBALS['TSFE']->sys_page->getRawRecord('pages', $id, $additionalFields);


			// now we need to see if this page is a redirect from the parent page
			// and loop while parentid is not null and the parent is still a redirect
		$parentId = $treeStartingRecord['pid'];
		while ($parentId > 0) {
			$parentRecord = $GLOBALS['TSFE']->sys_page->getRawRecord('pages', $parentId, $additionalFields);
	
			if ($parentRecord['doktype'] == 4 && ($parentRecord['shortcut'] == $id || $parentRecord['shortcut_mode'] > 0)) {
				$treeStartingRecord = $parentRecord;
				$parentId = $parentRecord['pid'];
				$id = $parentRecord['uid'];
			} else {
				break;
			}
		}

		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->addField('SYS_LASTCHANGED', 1);
		$tree->addField('crdate', 1);
			// remove "hide-in-menu" items
			// be aware: currently, this also removes the subpages that are below the hide-in-menu pages
			// but it's currently wanted by design
		if ($this->conf['renderHideInMenu'] != 1) {
			$addWhere = ' AND doktype != 5 AND nav_hide = 0';
		}
		$tree->init('AND no_search = 0 ' . $addWhere . $GLOBALS['TSFE']->sys_page->enableFields('pages'));


			// create the tree from starting point
		$tree->getTree($id, $depth, '');

			// creating the XML output
		$content = '';
		$usedUrls = array();

		$treeRecords = $tree->tree;
		array_unshift($treeRecords, array('row' => $treeStartingRecord));

		foreach ($treeRecords as $row) {
			$item = $row['row'];
				// don't render spacers, sysfolders etc
			if ($item['doktype'] >= 199) {
				continue;
			}
			$conf = array(
				'parameter' => $item['uid']
			);
				// also allow different languages
			if (!empty($GLOBALS['TSFE']->sys_language_uid)) {
				$conf['additionalParams'] = '&L=' . $GLOBALS['TSFE']->sys_language_uid;
			}

				// create the final URL
			$url  = $GLOBALS['TSFE']->cObj->typoLink_URL($conf);
			$urlParts = parse_url($url);
			if (!$urlParts['host']) {
				$url = $baseURL . ltrim($url, '/');
			}
			$url = htmlspecialchars($url);

			if (in_array($url, $usedUrls)) {
				continue;
			}
			$usedUrls[] = $url;

			$lastmod = ($item['SYS_LASTCHANGED'] ? $item['SYS_LASTCHANGED'] : $item['crdate']);

				// format date, see http://www.w3.org/TR/NOTE-datetime for possible formats
			$lastmod = date('c', $lastmod);

			$content .= '
	<url>
		<loc>'.$url.'</loc>
		<lastmod>'.$lastmod.'</lastmod>
	</url>';
		}

			// hook for adding additional urls
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seo_basics']['sitemap']['additionalUrlsHook'])) {
			$_params = array(
				'content' => &$content,
				'usedUrls' => &$usedUrls,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seo_basics']['sitemap']['additionalUrlsHook'] as $_funcRef) {
				t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}

			// see https://www.google.com/webmasters/tools/docs/en/protocol.html for complete format
		$content =
'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'.$content.'
</urlset>';

		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics_sitemap.php']) {
   include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics_sitemap.php']);
}
?>
