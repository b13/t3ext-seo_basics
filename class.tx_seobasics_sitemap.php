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


	protected $usedUrls = array();


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
	
				// check for shortcuts
			if ($this->conf['resolveMainShortcut'] == 1) {
				if ($parentRecord['doktype'] == 4 && ($parentRecord['shortcut'] == $id || $parentRecord['shortcut_mode'] > 0)) {
					$treeStartingRecord = $parentRecord;
					$id = $parentId = $parentRecord['pid'];
				} else {
					break;
				}
			} else {
					// just traverse the rootline up
				$treeStartingRecord = $parentRecord;
				$id = $parentId = $parentRecord['pid'];
			}
		}

		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->addField('SYS_LASTCHANGED', 1);
		$tree->addField('crdate', 1);
		$tree->addField('no_search', 1);
		$tree->addField('doktype', 1);
		$tree->addField('nav_hide', 1);

			// disable recycler and everything below
		$tree->init('AND doktype!=255' . $GLOBALS['TSFE']->sys_page->enableFields('pages'));


			// create the tree from starting point
		$tree->getTree($id, $depth, '');

		$treeRecords = $tree->tree;
		array_unshift($treeRecords, array('row' => $treeStartingRecord));

		foreach ($treeRecords as $row) {
			$item = $row['row'];

				// don't render spacers, sysfolders etc, and the ones that have the
				// "no_search" checkbox
			if ($item['doktype'] >= 199 || intval($item['no_search']) == 1) {
				continue;
			}

				// remove "hide-in-menu" items
			if ($this->conf['renderHideInMenu'] == 0 && intval($item['nav_hide']) == 1) {
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

			if (isset($this->usedUrls[$url])) {
				continue;
			}
			$lastmod = ($item['SYS_LASTCHANGED'] ? $item['SYS_LASTCHANGED'] : $item['crdate']);

				// format date, see http://www.w3.org/TR/NOTE-datetime for possible formats
			$lastmod = date('c', $lastmod);

			$this->usedUrls[$url] = array(
				'url' => $url,
				'lastmod' => $lastmod
			);
		}
		
		// check for additional pages
		$additionalPages = trim($this->conf['scrapeLinksFromPages']);
		if ($additionalPages) {
			$additionalPages = t3lib_div::trimExplode(',', $additionalPages, TRUE);
			if (count($additionalPages)) {
				$additionalSubpagesOfPages = $this->conf['scrapeLinksFromPages.']['includingSubpages'];
				$additionalSubpagesOfPages = t3lib_div::trimExplode(',', $additionalSubpagesOfPages);
				$this->fetchAdditionalUrls($additionalPages, $additionalSubpagesOfPages);
			}
		}


			// creating the XML output
		$content = '';

		
			// create the content
		foreach ($this->usedUrls as $urlData) {
			if ($urlData['lastmod']) {
				$lastmod = '
		<lastmod>' . htmlspecialchars($urlData['lastmod']) . '</lastmod>';
			} else {
				$lastmod = '';
			}

			$content .= '
	<url>
		<loc>' . htmlspecialchars($urlData['url']) . '</loc>' . $lastmod . '
	</url>';
		}


			// hook for adding additional urls
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seo_basics']['sitemap']['additionalUrlsHook'])) {
			$_params = array(
				'content' => &$content,
				'usedUrls' => &$this->usedUrls,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seo_basics']['sitemap']['additionalUrlsHook'] as $_funcRef) {
				t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}

			// see https://www.google.com/webmasters/tools/docs/en/protocol.html for complete format
		$content =
'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . $content . '
</urlset>';

		return $content;
	}
	
	
	/**
	 * fetches all URLs from existing pages + the subpages (1-level)
	 * and adds them to the $usedUrls array of the object
	 * 
	 * @param array $additionalPages
	 * @param array $additionalSubpagesOfPages array to keep track which subpages have been fetched already
	 */
	protected function fetchAdditionalUrls($additionalPages, $additionalSubpagesOfPages = array()) {

		$baseUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		foreach ($additionalPages as $additionalPage) {
			$newlyFoundUrls = array();
			if (in_array($additionalPage, $additionalSubpagesOfPages)) {
				$crawlSubpages = TRUE;
			} else {
				$crawlSubpages = FALSE;
			}

			$additionalPageId = intval($additionalPage);
			if ($additionalPageId) {
				$additionalUrl = $baseUrl . 'index.php?id=' . $additionalPageId;
			} else {
				$pageParts = parse_url($additionalPage);
				if (!$pageParts['scheme']) {
					$additionalUrl = $baseUrl . $additionalPage;
				} else {
					$additionalUrl = $additionalPage;
				}
			}
			$additionalUrl = htmlspecialchars($additionalUrl);
			$foundUrls = $this->fetchLinksFromPage($additionalUrl);

				// add the urls to the used urls
			foreach ($foundUrls as $url) {
				if (!isset($this->usedUrls[$url]) && !isset($this->usedUrls[$url . '/'])) {
					$this->usedUrls[$url] = array('url' => $url);
					if ($crawlSubpages) {
						$newlyFoundUrls[] = $url;
					}
				}
			}

				// now crawl the subpages as well
			if ($crawlSubpages) {
				foreach ($newlyFoundUrls as $subPage) {
					$foundSuburls = $this->fetchLinksFromPage($subPage);
					foreach ($foundSuburls as $url) {
						if (!isset($this->usedUrls[$url]) && !isset($this->usedUrls[$url . '/'])) {
							$this->usedUrls[$url] = array('url' => $url);
						}
					}
				}
			}
		}
	}
	

	/**
	 * function to fetch all links from a page
	 * by making a call to fetch the contents of the URL (via getURL)
	 * and then applying certain regular expressions
	 * 
	 * also takes "nofollow" into account (!)
	 * 
	 * @param string $url
	 * @return array the found URLs
	 */
	protected function fetchLinksFromPage($url) {
		$content = t3lib_div::getUrl($url);
		$foundLinks = array();

		$result = array();
		$regexp = '/<a\s+(?:[^"\'>]+|"[^"]*"|\'[^\']*\')*href=("[^"]+"|\'[^\']+\'|[^<>\s]+)([^>]+)/i';
		preg_match_all($regexp, $content, $result);

		$baseUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		foreach ($result[1] as $pos => $link) {
		
			if (strpos($result[2][$pos], '"nofollow"') !== FALSE || strpos($result[0][$pos], '"nofollow"') !== FALSE) {
				continue;
			}
		
			$link = trim($link, '"');
			list($link) = explode('#', $link);
			$linkParts = parse_url($link);
			if (!$linkParts['scheme']) {
				$link = $baseUrl . ltrim($link, '/');
			}

			if ($linkParts['scheme'] == 'javascript') {
				continue;
			}

			if ($linkParts['scheme'] == 'mailto') {
				continue;
			}
			
				// dont include files
			$fileName = basename($linkParts['path']);
			if (strpos($fileName, '.') !== FALSE && file_exists(PATH_site . ltrim($linkParts['path'], '/'))) {
				continue;
			}
			
			if ($link != $url) {
				$foundLinks[$link] = $link;
			}
		}
		return $foundLinks;
	}
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics_sitemap.php']) {
   include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics_sitemap.php']);
}

?>