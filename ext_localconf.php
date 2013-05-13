<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

	// adding th tx_seo_titletag to the pageOverlayFields so it is recognized when fetching the overlay fields
$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_seo_titletag,tx_seo_canonicaltag';

$extconf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['seo_basics']);


	// registering hook for correct indenting of output
if ($extconf['sourceFormatting'] == '1') {
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['tx_seobasics'] = 'EXT:seo_basics/class.tx_seobasics.php:&tx_seobasics->processOutputHook';
}


	// registering sitemap.xml for each hierachy of configuration to realurl (meaning to every website in a multisite installation)
if ($extconf['xmlSitemap'] == '1') {
	$realurl = $TYPO3_CONF_VARS['EXTCONF']['realurl'];
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
		$TYPO3_CONF_VARS['EXTCONF']['realurl'] = $realurl;
	}
}
