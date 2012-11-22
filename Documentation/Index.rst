..  Editor configuration
	...................................................
	* utf-8 with BOM as encoding
	* tab indentation with 4 characters (no space)
	* no wrapping when reaching the end of the margin, configuration with soft carriage return

.. Includes roles, substitutions, ...
.. include:: _Inclusion.rst

=================
SEO Basics
=================

:Extension name: SEO Basics
:Extension key: seo_basics
:Description: manuals covering TYPO3 basics
:Language: en
:Author: Benjamin Mack <benni@typo3.org>
:Creation date: 22-06-2012
:Generation date: |time|
:Licence: Open Content License available from http://www.opencontent.org/opl.shtml

The content of this document is related to TYPO3 - a GNU/GPL CMS/Framework available from www.typo3.org

.. toctree::
	:maxdepth: 2

	UserManual
	AdministratorManual
	TyposcriptReference
	DeveloperCorner
	ProjectInformation
	RestructuredtextHelp

.. STILL TO ADD IN THIS DOCUMENT
	@todo: add section about how screenshots can be automated. Pointer to PhantomJS could be added.
	@todo: explain how documentation can be rendered locally and remotely.
	@todo: explain what files should be versionned and what not (_build, Makefile, conf.py, ...)
	@todo: a word about inclusion

What does it do?
=================

First of all, if you have any idea how this template can be improved, please, drop a note to our team_. Documentation is written in reST format. Please, refer to Help writing reStructuredText to get some insight regarding syntax and existing reST editors on the market.

.. _team: http://forge.typo3.org/projects/typo3v4-official_extension_template/issues

Here should be given a brief overview of the extension. What does it do? What problem does it solve? Who is interested in this? Basically the document includes everything people need to know to decide, if they should go on with this extension.

.. figure:: Images/IntroductionPackage.png
		:width: 500px
		:alt: Introduction Package

		Introduction Package just after installation (caption of the image)

		How the Frontend of the Introduction Package looks like just after installation (legend of the image)