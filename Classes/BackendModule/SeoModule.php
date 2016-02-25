<?php

namespace B13\SeoBasics\BackendModule;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * SEO Management module
 */
class SeoModule extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule {

	/**
	 * loaded languages
	 * @var array
	 */
	protected $sysLanguages = array();

	/**
	 * @var bool
	 */
	protected $hasAvailableLanguages = false;

	/**
	 * if a language is selected
	 * @var bool
	 */
	protected $langOnly = false;

	/**
	 * @var array
	 */
	protected $langOverlays = array();

	/**
	 * @var array
	 */
	protected $pathCaches   = array();

	/**
	 * Does some initial work for the page
	 *
	 * @return	array
	 */
	public function init($pObj, $conf) {
		// load languages
		$trans = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Configuration\\TranslationConfigurationProvider');
		$this->sysLanguages = $trans->getSystemLanguages($pObj->id, $GLOBALS['BACK_PATH']);
		// see if multiple languages exist in the system (array includes more than "0" (default) and "-1" (all))
		$this->hasAvailableLanguages = (count($this->sysLanguages) > 2);

		parent::init($pObj, $conf);
	}

	/**
	 * Returns the menu array
	 *
	 * @return	array
	 */
	public function modMenu() {
		$langs = $this->sysLanguages;
		ksort($langs);
		if (!$langs[-1]['title']) {
			$langs[-1]['title'] = 'Show All Languages';
		}
		foreach ($langs as $k => $v) {
			$langs[$k] = $v['title'];
		}

		return array(
			'depth' => array(
				0  => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.depth_0'),
				1  => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.depth_1'),
				2  => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.depth_2'),
				3  => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.depth_3'),
				99 => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.depth_infi'),
			),
			'lang' => $langs,
			'hideShortcuts' => '',
			'hideSysFolders' => '',
			'hideNotInMenu' => '',
			'hideDisabled' => ''
		);
	}

	/**
	 * MAIN function for cache information
	 *
	 * @return	string		Output HTML for the module.
	 */
	public function main() {
		$content = '';

		// specific language selection from form
		$depth    = $this->pObj->MOD_SETTINGS['depth'];
		$langOnly = $this->pObj->MOD_SETTINGS['lang'];
		if ($langOnly != '' && $langOnly != '-1') {
			$this->langOnly = (int)$langOnly;
		}

		$id = (int)$this->pObj->id;
		if ($id) {

			// Add CSS
			$this->pObj->content = str_replace('/*###POSTCSSMARKER###*/','
				TABLE.c-list TR TD { white-space: nowrap; vertical-align: top; }
				TABLE#tx-seobasics TD { vertical-align: top; }
			',$this->pObj->content);


			// Add Javascript
			$this->pObj->doc->getPageRenderer()->loadJquery();
			$this->pObj->doc->getPageRenderer()->addJsFile(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('seo_basics') . 'Resources/Public/JavaScript/SeoModule.js');


			// render depth selector
			$content = BackendUtility::getFuncMenu($id, 'SET[depth]', $this->pObj->MOD_SETTINGS['depth'], $this->pObj->MOD_MENU['depth']);

			// if there are multiple languages, show dropdown to narrow it down.
			if ($this->hasAvailableLanguages) {
				$content .= 'Display only language:&nbsp;';
				$content .= BackendUtility::getFuncMenu($id, 'SET[lang]', $this->pObj->MOD_SETTINGS['lang'], $this->pObj->MOD_MENU['lang'], 'index.php').'<br/>';
			}

			$content .= BackendUtility::getFuncCheck($id, 'SET[hideShortcuts]', $this->pObj->MOD_SETTINGS['hideShortcuts'], '', '', 'id="SET[hideShortcuts]"');
			$content .= '<label for="SET[hideShortcuts]">Hide Shortcuts</label>&nbsp;&nbsp;';
			$content .= BackendUtility::getFuncCheck($id, 'SET[hideDisabled]', $this->pObj->MOD_SETTINGS['hideDisabled'], '', '', 'id="SET[hideDisabled]"');
			$content .= '<label for="SET[hideDisabled]">Hide Disabled Pages</label>&nbsp;&nbsp;<br/>'; 
			$content .= BackendUtility::getFuncCheck($id, 'SET[hideSysFolders]', $this->pObj->MOD_SETTINGS['hideSysFolders'], '', '', 'id="SET[hideSysFolders]"');
			$content .= '<label for="SET[hideSysfolders]">Hide System Folders</label>&nbsp;&nbsp;<br/>'; 	
			$content .= BackendUtility::getFuncCheck($id, 'SET[hideNotInMenu]', $this->pObj->MOD_SETTINGS['hideNotInMenu'], '', '', 'id="SET[hideNotInMenu]"');
			$content .= '<label for="SET[hideNotInMenu]">Hide Not in menu</label>&nbsp;&nbsp;<br/>'; 				

			// Save previous editing when submit was hit
			$this->saveChanges();


			// == Showing the tree ==

			// Initialize starting point (= $id) of page tree
			$treeStartingRecord = BackendUtility::getRecord('pages', $id);
			BackendUtility::workspaceOL('pages', $treeStartingRecord);

			// Initialize tree object
			$tree = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Tree\\View\\PageTreeView');
			$tree->addField('tx_seo_titletag', 1);
			$tree->addField('keywords', 1);
			$tree->addField('description', 1);
			$tree->addField('tx_realurl_pathsegment', 1);
			$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));


			// Creating top icon; the current page
			$HTML = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIconForRecord('pages', $treeStartingRecord);
			$tree->tree[] = array(
				'row' => $treeStartingRecord,
				'HTML' => $HTML
			);

			// Create the tree from starting point
			if ($depth > 0) {
				$tree->getTree($id, $depth, '');
			}


			// get all page IDs that will be displayed
			$pages = array();
			foreach($tree->tree as $row) {
				$pages[] = $row['row']['uid'];
			}

			// load language overlays and path cache for all pages shown
			$uidList = $GLOBALS['TYPO3_DB']->cleanIntList(implode(',', $pages));
			$this->loadLanguageOverlays($uidList);
			$this->loadPathCache($uidList);

			// Render information table
			$content .= $this->renderSaveButtons();
			$content .= $this->renderSEOTable($tree);
			$content .= $this->renderSaveButtons();
		}

		return $content;
	}


	/**
	 * Rendering the information
	 *
	 * @param array	$tree The Page tree data
	 * @return string HTML for the information table.
	 */
	protected function renderSEOTable($tree) {
		$cmd           = GeneralUtility::_GP('cmd');
		$hideShortcuts = ($this->pObj->MOD_SETTINGS['hideShortcuts'] == '1' ? true : false);
		$hideDisabled  = ($this->pObj->MOD_SETTINGS['hideDisabled']  == '1' ? true : false);
		$hideSysFolders  = ($this->pObj->MOD_SETTINGS['hideSysFolders']  == '1' ? true : false);
		$hideNotInMenu  = ($this->pObj->MOD_SETTINGS['hideNotInMenu']  == '1' ? true : false);

		// Traverse tree
		$output = '';
		foreach ($tree->tree as $row) {
			$itemHtml = $row['HTML'];
			$item     = $row['row'];
			$itemId   = $item['uid'];

			// filter checkbox selections
			if ($hideDisabled  && $item['hidden'] == 1) {
				continue;
			}
			if ($hideShortcuts && ($item['doktype'] == 3 || $item['doktype'] == 4 || $item['doktype'] == 199)) {
				continue;
			}
			if ($hideNotInMenu && $item['nav_hide'] == 1) {
				continue;
			}			
			if ($hideSysFolders && $item['doktype'] == 254) {
				continue;
			}

			// load translations for this record
			$translations = (is_array($this->langOverlays[$itemId]) ? $this->langOverlays[$itemId] : '');

			// multilanguage system
			$numRows = 1;
			if ($this->hasAvailableLanguages) {
				$item['sys_language'] = 0;

				// see if a specific language (and not the default one) is chosen
				// overwrite the default values, 
				if ($this->langOnly) {
					if (!is_array($translations[$this->langOnly])) continue;
					$item['uid'] 		 = $translations[$this->langOnly]['uid'];
					$item['tx_seo_titletag'] = $translations[$this->langOnly]['tx_seo_titletag'];
					$item['keywords']        = $translations[$this->langOnly]['keywords'];
					$item['description']     = $translations[$this->langOnly]['description'];
					$item['sys_language']    = $this->langOnly;
					$itemID = $item['uid'];
				}
				// if no specific language is chosen, display all translations of a page
				// in multirow format
				else if (is_array($translations)) {
					$numRows = count($translations)+1;
				}
			}

			// render main language (or specifically selected language) row
			$item['pathcache'] = $this->pathCaches[$itemId][($this->langOnly ? $this->langOnly : 0)];


			// row title
			$cellAttrib = ($item['_CSSCLASS'] ? ' class="'.$item['_CSSCLASS'].'"' : '');
			$rowTitle = '<td title="ID: '.$itemId.'"'.$cellAttrib.' nowrap="nowrap" ';
			if ($cmd == 'edit') {
				$rowTitle .= 'rowspan="'.($numRows*2).'">'.$itemHtml.BackendUtility::getRecordTitle('pages', $item, true).'</td>';
			} else {
				$rowTitle .= 'rowspan="'.$numRows.'">'.$itemHtml.BackendUtility::getRecordTitle('pages', $item, true).'</td>';
			}

			$tRows = $this->renderRowContent($item, $rowTitle);
	
			// compile row
			foreach ($tRows as $singleRow) {
				$output .= '<tr>' . implode('', $singleRow) . '</tr>';
			}

			// display other translations now
			if ($numRows > 1) {
				foreach ($translations as $langId => $item) {
					$item['sys_language'] = $langId;
					$item['pathcache'] = $this->pathCaches[$itemId][$langId];
					$tRows = $this->renderRowContent($item);
					// compile row
					foreach ($tRows as $singleRow) {
						$output.= '<tr>' . implode(LF,$singleRow) . '</tr>';
					}
				}
			}
		}

		// Create header
		$tCells = array();
		$tCells[] = '<td>Title&nbsp;</td>';

		if ($this->hasAvailableLanguages) {
			$tCells[] = '<td>Lang&nbsp;</td>';
		}

		if ($cmd != 'edit') {
			$tCells[] = '<td>URL&nbsp;Path</td>';
			$tCells[] = '<td>Title&nbsp;Tag</td>';
			$tCells[] = '<td>Keywords</td>';
			$tCells[] = '<td>Description</td>';
		} else {
			$tCells[] = '<td colspan="2">Title&nbsp;Tag</td>';
			$tCells[] = '<td colspan="2">Keywords</td>';
		}
		$output = '<tr class="bgColor5 tableheader t3-row-header">' . implode(LF, $tCells) . '</tr>' . $output;

		// Compile final table and return
		return '<table border="0" cellspacing="1" cellpadding="0" id="tx-seo_basics" class="lrPadding c-list typo3-dblist">' . $output . '</table>';
	}


	/**
	 * the function to render one row
	 * @param array $item the record
	 * @param string $rowTitle the row title
	 * @return array
	 */
	protected function renderRowContent($item, $rowTitle = NULL) {
		$cmd = GeneralUtility::_GP('cmd');
		$row1 = array();
		$row2 = array();

		if ($cmd != 'edit') {
			$row1[] = $item['pathcache'];
			$row1[] = $item['tx_seo_titletag'];
			$row1[] = $item['keywords'];
			$row1[] = $item['description'];

			// before output, wrap each cell in tds
			foreach ($row1 as $k => $v) {
				$row1[$k] = '<td>'.htmlspecialchars($v).'</td>';
			}
		} else {
			// display fields that can be edited
			$tbl = ($item['sys_language'] > 0 ? 'pages_language_overlay' : 'pages');
			$fName = 'tx_seo[' . $tbl . '][' . $item['uid'] . ']';
			$row1[] = '<td>Title-Tag:</td><td><input name="' . $fName . '[tx_seo_titletag]" value="' . htmlspecialchars($item['tx_seo_titletag']) . '" type="text" size="43" maxlength="100" autocomplete="off" class="seoTitleTag"/></td><td>Keywords:</td><td><input name="' . $fName . '[keywords]" value="' . htmlspecialchars($item['keywords']) . '" type="text" size="67" maxlength="180" autocomplete="off" class="seoKeywords"/></td>';
			$row2[] = '<td>Description:</td><td colspan="3"><input name="' . $fName . '[description]" value="' . htmlspecialchars($item['description']) . '" type="text" size="120" autocomplete="off" class="seoDescription"/><br/><br/></td>';
		}

		if ($this->hasAvailableLanguages) {
			array_unshift($row1, '<td ' . ($cmd == 'edit' ? 'rowspan="2"' : '').'>' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon($this->sysLanguages[$item['sys_language']]['flagIcon']) . '</td>');
		}
		if ($rowTitle) {
			array_unshift($row1, $rowTitle);
		}
		return (count($row2) ? array($row1, $row2) : array($row1));
	}


	/**
	 * This function loads all language overlays that exist for pages 
	 * an local array
	 * @param array $uidList The Page Uids
	 * @return void
     */
	protected function loadLanguageOverlays($uidList) {
		// no localization
		if (!$this->hasAvailableLanguages) {
			return;
		}
		// if the main language is selected, don't load overlay records
		if ($this->langOnly === 0) {
			return;
		}

		// building where clause
		$where = ($this->langOnly ? ' AND sys_language_uid = ' . $this->langOnly : '');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, pid, sys_language_uid, title, tx_seo_titletag, keywords, description',
			'pages_language_overlay',
			'pid IN ('.$uidList .') ' . $where . BackendUtility::BEenableFields('pages_language_overlay'),
			'',
			'pid ASC, sys_language_uid ASC'
		);

			// fill results into instance variable
		$this->langOverlays = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$item = array(
				'uid' => $row['uid'],
				'title' => $row['title'],
				'tx_seo_titletag' => $row['tx_seo_titletag'],
				'keywords' => $row['keywords'],
				'description' => $row['description']
			);
			$this->langOverlays[$row['pid']][$row['sys_language_uid']] = $item;
		}
	}


	/**
	 * Loads the path cache from the RealURL extension table to
	 * display it later in the table
	 *
	 * @param string $uidList The Page Uids
	 * @return void
     */
	protected function loadPathCache($uidList) {

		// building where clause
		$where = ($this->langOnly || $this->langOnly === 0 ? ' AND language_id = ' . $this->langOnly : '');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'page_id, language_id, pagepath, cache_id ',
			'tx_realurl_pathcache',
			'page_id IN ('. $uidList .') ' . $where,
			'',
			'language_id ASC, expire ASC'
		);

		// Traverse result
		$this->pathCaches = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->pathCaches[$row['page_id']][$row['language_id']] = $row['pagepath'];
		}
	}


	/**
	 * Render edit / save buttons
	 *
	 * @return string the HTML
	 */
	protected function renderSaveButtons() {
		$cmd = GeneralUtility::_GP('cmd');
		if ($cmd == 'edit') {
			$output = '<br/>
			<input type="submit" name="submit"  value="Save Changes" />
			<input type="button" value="Cancel" onclick="jumpToUrl(\''.$this->linkToSelf().'\');" / />
			<input type="hidden" name="cmd"     value="edit" />
			<input type="hidden" name="id"      value="'.htmlspecialchars($this->pObj->id).'" /><br/><br/>';
		}
		else {
			$output = '<br/>
			<input type="button" value="Edit SEO fields" onclick="jumpToUrl(\''.$this->linkToSelf('cmd=edit').'\');" /><br/><br/>';
		}
		return $output;
	}

	/**
	 * Links to the module script and sets necessary parameters
	 *
	 * @param string $addParams Additional GET vars
	 * @return string script + query params
	 */
	protected function linkToSelf($addParams = '') {

		$langOnly      = $this->langOnly;
		$hideShortcuts = ($this->pObj->MOD_SETTINGS['hideShortcuts'] == '1' ? true : false);
		$hideDisabled  = ($this->pObj->MOD_SETTINGS['hideDisabled']  == '1' ? true : false);

		if ($addParams && strpos('&', $addParams) !== 0) { $addParams = '&'.$addParams; }
		if ($langOnly) { $addParams .= '&langOnly='.$langOnly; }
		if ($hideShortcuts) { $addParams .= '&hideShortcuts='.$hideShortcuts; }
		if ($hideDisabled) { $addParams .= '&hideDisabled='.$hideDisabled; }

		return BackendUtility::getModuleUrl(GeneralUtility::_GET('M'), array('id' => $this->pObj->id)) . $addParams;
	}


	/**
	 * Will look for submitted SEO / page entries to save to DB
	 *
	 * @todo: use DataHandler or tce_db.php for that
	 * @return	void
	 */
	protected function saveChanges() {
		$seoData = GeneralUtility::_POST('tx_seo');
		if (!is_array($seoData)) {
			return;
		}

		// run through every table (can only be "pages" or "pages_language_overlay")
		foreach($seoData as $tbl => $res) {
			if ($tbl !== 'pages' && $tbl !== 'pages_language_overlay') {
				continue;
			}

			$emptyItems = array();

			// run through every item in the table
			foreach ($res as $uid => $item) {
				$uid = intval($uid);
				if (empty($item['tx_seo_titletag']) && empty($item['keywords']) && empty($item['description'])) {
					$emptyItems[] = $uid;
					continue;
				}

				$fields = array(
					'tx_seo_titletag' => $item['tx_seo_titletag'],
					'keywords'        => $item['keywords'],
					'description'     => $item['description']
				);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($tbl,'uid = '.$uid,$fields);
			}

			// set all items where all fields all empty at once to save time
			if (count($emptyItems)) {
				$uidList = $GLOBALS['TYPO3_DB']->cleanIntList(implode(',', $emptyItems));

				$fields = array(
					'tx_seo_titletag' => '',
					'keywords'        => '',
					'description'     => ''
				);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($tbl,'uid IN ('.$uidList.')',$fields);
			}
		}
	}
}
