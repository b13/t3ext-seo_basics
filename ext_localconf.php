<?php
defined('TYPO3_MODE') or die();

// adding th tx_seo_titletag to the pageOverlayFields so it is recognized when fetching the overlay fields
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'] .= ',tx_seo_titletag,tx_seo_canonicaltag';

$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seo_basics']);

	// registering sitemap.xml for each hierachy of configuration to realurl (meaning to every website in a multisite installation)
if ($extensionConfiguration['xmlSitemap'] == '1') {
	$realurl = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'];
	if (is_array($realurl))	{
		foreach ($realurl as $host => $cnf) {
			// we won't do anything with string pointer (e.g. example.org => www.example.org)
			if (!is_array($realurl[$host])) {
				continue;
			}
			
			if (!isset($realurl[$host]['fileName'])) {
				$realurl[$host]['fileName'] = array();
			}
			$realurl[$host]['fileName']['index']['sitemap.xml']['keyValues']['type'] = 776;
		}
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] = $realurl;
	}
}
