<?php

namespace B13\SeoBasics\Service;

/***************************************************************
 *  Copyright notice - MIT License (MIT)
 *
 *  (c) 2007-2014 Benni Mack <benni@typo3.org>
 *  All rights reserved
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 ***************************************************************/

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UrlService provides some PHP functionality to detect
 * the current URL
 *
 * @package B13\SeoBasics\Controller
 */
class UrlService {

	/**
	 * Returns the URL for the current webpage
	 *
	 * @param $content string The content (usually empty)
	 * @param $conf array The TypoScript configuration
	 * @return string the canonical URL of this page
	 */
	public function getCanonicalUrl($content, $conf) {
		if ($this->getFrontendController()->page['tx_seo_canonicaltag']) {
			$url = $this->getFrontendController()->page['tx_seo_canonicaltag'];
		} else {
			$pageId = $this->getFrontendController()->id;
			$pageType = $this->getFrontendController()->type;
			$mountPointInUse = FALSE;
			$MP = '';

			if ($this->getFrontendController()->MP) {
				$mountPointInUse = TRUE;
				$GLOBALS['TYPO3_CONF_VARS']['FE']['enable_mount_pids'] = 0;
				$MP = $this->getFrontendController()->MP;
				$this->getFrontendController()->MP = '';
			}

			$configuration = array(
				'parameter' => $pageId . ',' . $pageType,
				'addQueryString' => 1,
				'addQueryString.' => array(
					'method' => 'GET',
					'exclude' => 'MP'
				),
				'forceAbsoluteUrl' => 1
			);
			$url = $this->getFrontendController()->cObj->typoLink_URL($configuration);
			$url = $this->getFrontendController()->baseUrlWrap($url);

			if ($mountPointInUse) {
				$this->getFrontendController()->MP = $MP;
				$GLOBALS['TYPO3_CONF_VARS']['FE']['enable_mount_pids'] = 1;
			}

		}

		if ($url) {
			$urlParts = parse_url($url);
			$scheme = $urlParts['scheme'];
			if (isset($conf['useDomain'])) {
				if ($conf['useDomain'] == 'current') {
					$domain = GeneralUtility::getIndpEnv('HTTP_HOST');
				} else {
					$domain = $conf['useDomain'];
				}
				if (!$scheme) {
					$scheme = 'http';
				}
    			$url =  $scheme . '://' . $domain . $urlParts['path'];
			} elseif (empty($urlParts['scheme'])) {
				$pageWithDomains = $this->getFrontendController()->findDomainRecord();
				// get first domain record of that page
				$allDomains = $this->getFrontendController()->sys_page->getRecordsByField(
					'sys_domain',
					'pid', $pageWithDomains,
					'AND redirectTo = ""' . $this->getFrontendController()->sys_page->enableFields('sys_domain'),
					'',
					'sorting ASC'
				);
				if (!empty($allDomains)) {
					$domain = (GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://');
					$domain = $domain . $allDomains[0]['domainName'];
					$domain = rtrim($domain, '/') . '/' . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH');
				} else {
					$domain = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
				}
				$url = rtrim($domain, '/') . '/' . ltrim($url, '/');
			}
				// remove everything after the ?
			list($url) = explode('?', $url);
		}
		return $url;
	}

	/**
	 * wrapper function for the current TSFE object
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected function getFrontendController() {
		return $GLOBALS['TSFE'];
	}
}