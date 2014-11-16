#
# Add fields for table 'pages'
#
CREATE TABLE pages (
	tx_seo_titletag tinytext,
	tx_seo_canonicaltag tinytext
);

#
# Add fields for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
	tx_seo_titletag tinytext,
	tx_seo_canonicaltag tinytext
);
