<?php

// Adding a static template TypoScript configuration from static/ (deprecated)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('seo_basics', 'static', 'Metatags and XML Sitemap (old), simple replaced by new one');

// Adding the static template for new TypoScript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('seo_basics', 'Configuration/TypoScript', 'Metatags and XML Sitemap');
