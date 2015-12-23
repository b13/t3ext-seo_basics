# SEO Basics - A TYPO3 Extension

## Introduction

SEO Basics is a TYPO3 CMS extension installable via TYPO3.org/TER and the extension manager.

It is compatible with TYPO3 CMS 6.2+. You can get it here: http://typo3.org/extensions/repository/view/seo_basics

## What does it do?

This extension bundles most SEO features needed for getting a simple website going with search engine optimizations.

Well – so what does it do? - It adds an extra field to every page for manipulating the title-tag of the page.
Since this is – next to the content – the most important data for a search engine of a web page, it deserves an extra
field. If the field is left blank for a page, the regular page title will be used.

Also, the fields keywords and description are now available for the page type "standard". Also, keywords and description
for a page as well as the last time a page was changed will be added to the output automatically.
It does not add DC tags since we believe that search engines don't care about it that much.

A new submodule under "Web" => "Info" is added to have an overview over all title-tags, all keywords and all
descriptions. It is possible to edit all fields at once, which is nice for comparing. When in
editing mode this page shows where there are enough (or maybe too many) keywords, and colors the
background of every field depending on the length of the content.

A new tag called the "Canonical Tag", a tag in the head area of the web page that the search engines wanted
to avoid duplicated content.

SEO Basics also relies heavily on the realurl extension, adding a new page type that is redirected automatically when
google asks for sitemap.xml file. Right after the installation of the SEO extension there is a new page type available
that is mapped to "sitemap.xml" which means that if you enter www.your-typo3-page.com/sitemap.xml that you'll get the
google sitemap / XML sitemap for all your pages.

There is no need for an extra "google sitemap" extension, no need for an extra “metatags” extension.


## Why did we create this extension?

Well, first of all... we did quite some research and did some tests with pages to see how manipulating e.g. the page
title or the description affects the ranking and also the output on the search result pages. We then found out what is
important for the search engines and what they care about. Then we tried out several extensions but we noticed that they
are not quite usable e.g. several googlesitemap extensions weren't working as expected and not out of the box.
We also wanted one extension that does almost most of the things out of the box, so there's not a lot to configure.
We wanted to have an extension to get new installations up and running with SEO pretty fast, without a lot of
configuration and without dozens of not-quite-perfect extensions.


## ToDo

* Check all labels
* Release 1.0
* Add new og: fields
* Come up with a new backend module


## Credits

Pull Requests are very welcome!

* Thanks to EDV-Sachverständigenbüro Weißleder Stuttgart (www.weissleder.de) and ITANA (www.itana.com) for sponsoring the initial development of this extension.
* Thanks to b13 and its clients to sponsor the further development of this extension.
* Thanks to the TYPO3 community for giving feedback on the extension.
* Thanks to Jesus Christ who gave me the power and energy to live and therefore to write this extension.
