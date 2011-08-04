<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Nils K. Windisch <windisch@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
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
 * ************************************************************* */
require_once(t3lib_extMgm::extPath('nkwlib') . 'class.tx_nkwlib.php');

/**
 * Plugin 'SUB Menu' for the 'nkwsubmenu' extension.
 *
 * @author Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @author Ingo Pfennigstorf <pfennigstorf@sub.uni-goettingen.de>
 * @package TYPO3
 * @subpackage tx_nkwsubmenu
 * $Id$
 */
class tx_nkwsubmenu_pi1 extends tslib_pibase {

	public $prefixId = 'tx_nkwsubmenu_pi1';
	public $scriptRelPath = 'pi1/class.tx_nkwsubmenu_pi1.php';
	public $extKey = 'nkwsubmenu';
	public $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content The PlugIn content
	 * @param array $conf The PlugIn configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

			// Embed Javascript of this extension
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId. '-1'] = '<script type="text/javascript" src="/typo3conf/ext/nkwsubmenu/pi1/res/js/menu.js"></script>';
		
			// Javascript der JK-Navigation einbinden
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId. '-2'] .= '<script type="text/javascript" src="/typo3conf/ext/nkwsubmenu/pi1/res/js/jknav.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId. '-3'] .= '<script type="text/javascript" src="/typo3conf/ext/nkwsubmenu/pi1/res/js/hotkeys.js"></script>';
			// Konfigurieren der JK-Navigation
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId. '-4'] .= '<script type="text/javascript" src="/typo3conf/ext/nkwsubmenu/pi1/res/js/jkrun.js"></script>';

			// page ID
		$weAreHerePageId = $GLOBALS['TSFE']->id;
			// T3 hack
		$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;

		$lang = tx_nkwlib::getLanguage();
		$knot = tx_nkwlib::knotId($weAreHerePageId);
		$pageInfo = tx_nkwlib::pageInfo($weAreHerePageId, $lang);
		/* FIRST LEVEL
		 * query
		 * get all pages which are menu items (tx_nkwsubmenu_in_menu = '1')
		 * or those who are not, but contain a knot (tx_nkwsubmenu_in_menu = '3')
		 */
                if ($knot) {
			$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
							'pages',
							'uid = ' . $knot . ' AND hidden != 1 AND deleted = 0',
							'',
							'sorting ASC',
							''
							);
		} else {
			$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
							'pages',
							'(tx_nkwsubmenu_in_menu = 1 OR tx_nkwsubmenu_in_menu = 3) AND hidden != 1 AND deleted = 0',
							'',
							'sorting ASC',
							'');
		}
			// helper
		$i1 = 0;
			// cycle
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1)) {
			$pages[$i1]['uid'] = $row1['uid'];
			$pages[$i1]['pid'] = $row1['pid'];
			$pages[$i1]['title'] = tx_nkwlib::formatString($row1['title']);

				// we don't use the default language, so get the alternative page title
			if ($lang > 0) {
				$pages[$i1]['title'] = tx_nkwlib::formatString($this->getPageTitle($row1['uid'], $lang));
			}
			$pages[$i1]['tx_nkwsubmenu_in_menu'] = $row1['tx_nkwsubmenu_in_menu'];
			$pages[$i1]['tx_nkwsubmenu_picture'] = $row1['tx_nkwsubmenu_picture'];
			$pages[$i1]['tx_nkwsubmenu_knotheader'] = $row1['tx_nkwsubmenu_knotheader'];
			$pages[$i1]['tx_nkwsubmenu_picture_follow'] = $row1['tx_nkwsubmenu_picture_follow'];
			$pages[$i1]['tx_nkwsubmenu_usecontent'] = $row1['tx_nkwsubmenu_usecontent'];
				// check if node
			if ($row1['tx_nkwsubmenu_knot']) {
				$pages[$i1]['isKnot'] = 1;
			}
				// check if selected
			if ($row1['uid'] == $weAreHerePageId) {
				$pages[$i1]['selected'] = 1;
			}
			if ($pages[$i1]['tx_nkwsubmenu_usecontent']) {
					// query
				$resContent = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								'tt_content',
								'pid = ' . $row1['uid'] . ' AND hidden = 0 AND deleted = 0 AND sys_language_uid = ' . $lang,
								'',
								'sorting ASC',
								''
				);

				$i = 0;
				while ($rowContent = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resContent)) {
					$arrContent[$i]['header'] = $rowContent['header'];
					$arrContent[$i]['uid'] = $rowContent['uid'];
					$i++;
				}
				$pages[$i1]['content'] = $arrContent;
			}
				// SECOND LEVEL
				// query
			$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
			'pages',
			'pid = ' . $row1['uid'] . ' AND tx_nkwsubmenu_in_menu != 2 AND hidden = 0 AND deleted = 0',
			'',
			'sorting ASC',
			'');

				// helper
			$i2 = 0;

				// cycle
			while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
				$pages[$i1]['child'][$i2]['uid'] = $row2['uid'];
				$pages[$i1]['child'][$i2]['title'] = tx_nkwlib::formatString($row2['title']);
				if ($lang > 0) {
					$pages[$i1]['child'][$i2]['title'] = tx_nkwlib::formatString($this->getPageTitle($row2['uid'], $lang));
				}
				$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture'] = $row2['tx_nkwsubmenu_picture'];
				$pages[$i1]['child'][$i2]['tx_nkwsubmenu_knotheader'] = $row2['tx_nkwsubmenu_knotheader'];
				$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture_follow'] = $row2['tx_nkwsubmenu_picture_follow'];
				$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture_nofollow'] = $row2['tx_nkwsubmenu_picture_nofollow'];
				$pages[$i1]['hasChild'] = 1;

					// pictures
				if (!$row2['tx_nkwsubmenu_picture']
						&& !$row2['tx_nkwsubmenu_picture_nofollow']
						&& $row1['tx_nkwsubmenu_picture']
						&& $row1['tx_nkwsubmenu_picture_follow']) {
					$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture'] = $row1['tx_nkwsubmenu_picture'];
					$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture_follow'] = 1;
				}

					// node
				if ($row2['tx_nkwsubmenu_knot']) {
					$pages[$i1]['child'][$i2]['isKnot'] = 1;
					$pages[$i1]['hasKnot'] = 1;
				}

					// selected
				if ($row2['uid'] == $weAreHerePageId) {
					$pages[$i1]['selected'] = 2;
					$pages[$i1]['child'][$i2]['selected'] = 1;
				}

					// check if second level item is within node
				if ($row1['tx_nkwsubmenu_knot']) {
					$pages[$i1]['child'][$i2]['inKnot'] = 1;
				}
				if ($row2['uid'] == $weAreHerePageId) {
					$pages[$i1]['hasActiveKnot'] = 1;
				}

					// THIRD LEVEL
					// query
				$res3 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								'pages',
								'pid = ' . $row2['uid'] . ' AND tx_nkwsubmenu_in_menu != 2 AND hidden = 0 AND deleted = 0',
								'',
								'sorting ASC',
								''
				);

					// helper
				$i3 = 0;

					// cycle
				while ($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res3)) {
					$pages[$i1]['child'][$i2]['child'][$i3]['uid'] = $row3['uid'];
					$pages[$i1]['child'][$i2]['child'][$i3]['title'] = tx_nkwlib::formatString($row3['title']);
					if ($lang > 0) {
						$pages[$i1]['child'][$i2]['child'][$i3]['title'] = tx_nkwlib::formatString(
										$this->getPageTitle($row3['uid'], $lang));
					}
					$pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_picture'] = $row3['tx_nkwsubmenu_picture'];
					$pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_knotheader'] = $row3['tx_nkwsubmenu_knotheader'];
					$pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_picture_follow'] = $row3['tx_nkwsubmenu_picture_follow'];
					$pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_picture_nofollow'] = $row3['tx_nkwsubmenu_picture_nofollow'];
					$pages[$i1]['child'][$i2]['hasChild'] = 1;

						// pictures
					if (!$row3['tx_nkwsubmenu_picture']
							&& !$row3['tx_nkwsubmenu_picture_nofollow']
							&& $pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture']
							&& $pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture_follow']) {
						$pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_picture'] = $pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture'];
						$pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_picture_follow'] = 1;
					}

						// node
					if ($row3['tx_nkwsubmenu_knot']) {
						$pages[$i1]['child'][$i2]['child'][$i3]['isKnot'] = 1;
						$pages[$i1]['child'][$i2]['hasKnot'] = 1;
						$pages[$i1]['hasKnot'] = 1;
					}
					if ($row3['uid'] == $weAreHerePageId) {
						$pages[$i1]['child'][$i2]['hasActiveKnot'] = 1;
						$pages[$i1]['hasActiveKnot'] = 1;
					}

						// selected
					if ($row3['uid'] == $weAreHerePageId) {
						$pages[$i1]['selected'] = 3;
						$pages[$i1]['child'][$i2]['selected'] = 2;
						$pages[$i1]['child'][$i2]['child'][$i3]['selected'] = 1;
					}

						// check if third level item is within knot
					if ($pages[$i1]['child'][$i2]['isKnot'] || $pages[$i1]['child'][$i2]['inKnot']) {
						$pages[$i1]['child'][$i2]['child'][$i3]['inKnot'] = 1;
					}

						// check for page on level 4 and display parent levels as open in menu
					$i4 = 0;
					$res4 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'*',
					'pages',
					'pid = ' . $pages[$i1]['child'][$i2]['child'][$i3]['uid'] .' AND tx_nkwsubmenu_in_menu != 2 AND hidden = 0 AND deleted = 0',
									'',
									'sorting ASC',
									''
					);
					while ($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res4)) {
						$pages[$i1]['child'][$i2]['child'][$i3]['child'][$i4]['uid'] = $row4['uid'];
						$pages[$i1]['child'][$i2]['child'][$i3]['child'][$i4]['title'] = $row4['title'];
						if ($weAreHerePageId == $row4['uid']) {
							$pages[$i1]['child'][$i2]['child'][$i3]['selected'] = 1;
							$pages[$i1]['child'][$i2]['selected'] = 2;
							$pages[$i1]['selected'] = 3;
						}
						$i4++;
					}

						// helper
					$i3++;
				}

					// helper
				$i2++;
			}

				// helper
			$i1++;
		}

			// helper
		$pagesSize = $i1;

			// set banner picture
		for ($i = 0; $i < $pagesSize; $i++) {

				// FIRST
			if ($pages[$i]['selected'] == 1) {
				$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i]['tx_nkwsubmenu_picture'];
			} elseif ($pages[$i]['selected'] >= 2) {
					// set picture
				if ($pages[$i]['hasChild']) {
						// SECOND
					$countChildPages = count($pages[$i]['child']);

					for ($ii = 0; $ii < $countChildPages; $ii++) {
						if ($pages[$i]['child'][$ii]['selected'] == 1) {
								// set picture
							$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i]['child'][$ii]['tx_nkwsubmenu_picture'];
						} elseif ($pages[$i]['child'][$ii]['selected'] == 2) {
								// THIRD
							$countChildChildPages = count($pages[$i]['child'][$ii]['child']);
							for ($iii = 0; $iii < $countChildChildPages; $iii++) {
								if ($pages[$i]['child'][$ii]['child'][$iii]['selected'] == 1) {
										// set picture
									$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i]['child'][$ii]['child'][$iii]['tx_nkwsubmenu_picture'];
								}
							}
						}
					}
				}
			}
		}
			// set banner picture
			// if is node
		if ($knot) {
				// remove everything from first level that has no active knot
			for ($i = 0; $i < $pagesSize; $i++) {
				if ($pages[$i]['isKnot'] || $pages[$i]['hasActiveKnot']) {

						// tmp save array index with active knot
					$tmp = $pages[$i];

						// reset pages array
					unset($pages);

						// set pages array with active knot index only
					$pages[0] = $tmp;
				}
			}

				// remove everything from second level that has no active knot
			$countPageChilds = count($pages[0]['child']);
			for ($ii = 0; $ii < $countPageChilds; $ii++) {
				if ($pages[0]['child'][$ii]['isKnot'] && $pages[0]['child'][$ii]['selected']) {

						// tmp save array index with active knot
					$tmp = $pages[0]['child'][$ii];

						// reset pages array
					unset($pages[0]['child']);

						// set pages array with active knot index only
					$pages[0] = $tmp;
				}
			}

				// remove everything from third level that has no active knot
			for ($i = 0; $i < sizeof($pages[0]['child']); $i++) {
				if ($pages[0]['child'][$i]['isKnot']) {

						// tmp save array index with active knot
					$tmp = $pages[0]['child'][$i];

						// reset pages array
					unset($pages[0]['child']);

						// set pages array with active knot index only
					$pages[0] = $tmp;
				}
			}
		}
			// remove pages who contain knots (not active), but don't show up in the menu
		if (!$knot) {
			for ($i = 0; $i < $pagesSize; $i++)
				if ($pages[$i]['tx_nkwsubmenu_in_menu'] == 1) {
					$tmp[] = $pages[$i];
				}
			$pages = $tmp;
		}
		$pagesSize = sizeof($pages);
			// FIRST
		for ($i1 = 0; $i1 < $pagesSize; $i1++) {
			if ($pages[$i1]['selected'] == 1) {
				$menuContent .= '<li class="tx-nkwsubmenu-pi1-selected">';
			} else {
				$menuContent .= "\t\t<li>";
			}
				// $menuContent .= '<span>';
				// format link
			if ($pages[$i1]['selected'] == 1) {
				if ($pages[$i1]['hasChild'] && !$pages[$i1]['isKnot']) {
					$menuContent .= '<a class="tx-nkwsubmenu-pi1-highlight tx-nkwsubmenu-pi1-trigger">' . $pages[$i1]['title'] . '</a>';
				} else {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['title'] . '" class="tx-nkwsubmenu-pi1-highlight tx-nkwsubmenu-pi1-trigger"';
					$menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
				}
			} elseif ($pages[$i1]['selected'] == 2 || $pages[$i1]['selected'] == 3) {
				if ($pages[$i1]['hasChild'] && !$pages[$i1]['isKnot']) {
					$menuContent .= '<a title="' . $pages[$i1]['title'] . '" class="tx-nkwsubmenu-pi1-highlight-parent tx-nkwsubmenu-pi1-trigger">' . $pages[$i1]['title'] . '</a>';
				} else {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['title'] . '" class="tx-nkwsubmenu-pi1-highlight-parent tx-nkwsubmenu-pi1-trigger"';
					$menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
				}
			} else {
				if ($pages[$i1]['hasChild']) {
					$menuContent .= '<a title="' . $pages[$i1]['title'] . '" class="tx-nkwsubmenu-pi1-trigger">' . $pages[$i1]['title'] . '</a>';
				} else {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['title'] . '" class="tx-nkwsubmenu-pi1-trigger"';
					$menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
				}
			}
			if ($pages[$i1]['tx_nkwsubmenu_usecontent']) {
					// open ul if selected
				if ($pages[$i1]['selected']) {
					$menuContent .= "\n\t<ul class='tx-nkwsubmenu-pi1-l2 js go'>\n";
				} else {
					$menuContent .= "\n\t<ul class='tx-nkwsubmenu-pi1-l2 js'>\n";
				}
				foreach ($pages[$i1]['content'] AS $key => $value) {
					$menuContent .= "\t\t<li>";
					$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;
					$GLOBALS['TSFE']->ATagParams = 'title="' . $value['header'] . '"';
					$menuContent .= $this->pi_LinkToPage(
									$value['header'], $pages[$i1]['uid'] . '#c' . $value['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
					$menuContent .= "\t\t</li>";
				}
				$menuContent .= "\n\t</ul>";
			}
				// SECOND
			if ($pages[$i1]['hasChild'] && (!$pages[$i1]['isKnot'] || $pages[$i1]['selected'])) {
					// open ul if selected
				if ($pages[$i1]['selected']) {
					$menuContent .= "\n\t<ul class='tx-nkwsubmenu-pi1-l2 js go'>\n";
				} else {
					$menuContent .= "\n\t<ul class='tx-nkwsubmenu-pi1-l2 js'>\n";
				}
					// cycle children
				for ($i2 = 0; $i2 < sizeof($pages[$i1]['child']); $i2++) {
					if ($pages[$i1]['child'][$i2]['selected'] == 1) {
						$menuContent .= "\t\t<li class='tx-nkwsubmenu-pi1-selected'>";
						// set banner picture
					} else {
						$menuContent .= "\t\t<li>";
					}
						// format link
					if ($pages[$i1]['child'][$i2]['selected'] == 1) {
						$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['title'] . '" class="tx-nkwsubmenu-pi1-highlight"';
						$menuContent .= $this->pi_LinkToPage(
										$pages[$i1]['child'][$i2]['title'], $pages[$i1]['child'][$i2]['uid'], '', '');
						$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
					} elseif ($pages[$i1]['child'][$i2]['selected'] == 2) {
						$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;
						$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['title'] . '"';
						$menuContent .= $this->pi_LinkToPage(
										$pages[$i1]['child'][$i2]['title'], $pages[$i1]['child'][$i2]['uid'], '', '');
						$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
					} else {
						$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;
						$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['title'] . '"';
						$menuContent .= $this->pi_LinkToPage(
										$pages[$i1]['child'][$i2]['title'], $pages[$i1]['child'][$i2]['uid'], '', '');
						$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
					}
						// THIRD
					if ($pages[$i1]['child'][$i2]['hasChild']
							&& (!$pages[$i1]['child'][$i2]['isKnot'] || $pages[$i1]['child'][$i2]['selected'])) {
						$menuContent .= "\n\t\t\t<ul class='tx-nkwsubmenu-pi1-l3'>\n";
							// cycle children
						for ($i3 = 0; $i3 < sizeof($pages[$i1]['child'][$i2]['child']); $i3++) {
								// format link
							if ($pages[$i1]['child'][$i2]['child'][$i3]['selected'] == 1) {
								$menuContent .= "\t\t\t\t<li class='tx-nkwsubmenu-pi1-selected'>";
								$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['child'][$i3]['title'] . '" class="tx-nkwsubmenu-pi1-highlight"';
								$menuContent .= $this->pi_LinkToPage(
												$pages[$i1]['child'][$i2]['child'][$i3]['title'], $pages[$i1]['child'][$i2]['child'][$i3]['uid'], '', '');
								$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
								// set banner picture
							} else {
								$menuContent .= "\t\t\t\t<li>";
								$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;
								$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['child'][$i3]['title'] . '"';
								$menuContent .= $this->pi_LinkToPage(
												$pages[$i1]['child'][$i2]['child'][$i3]['title'], $pages[$i1]['child'][$i2]['child'][$i3]['uid'], '', '');
								$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
							}
							$menuContent .= "</li>\n";
						}
						$menuContent .= "\t\t\t</ul>\n\t\t";
					}
					$menuContent .= "</li>\n";
				}
				$menuContent .= "\t</ul>\n";
			}
			$menuContent .= "</li>\n";
		}
			// wrap everything and go
		$content = '<ul id="menu1" class="tx-nkwsubmenu-pi1-l1 expand">' . $menuContent . $contentTmp . '</ul>';
			// show a back to startpage link if in knot
		if ($knot) {
			$pageInfo = tx_nkwlib::pageInfo(1, $lang);
			$GLOBALS['TSFE']->ATagParams = 'title="' . $pageInfo['title'] . '" class="tx-nkwsubmenu-pi1-trigger"';
			$content = '<ul><li>' . $this->pi_LinkToPage($pageInfo['title'], 1, '', '') . '</li></ul>' . $content;
				// T3 hack
			$GLOBALS['TSFE']->ATagParams = $saveAnchorTagParams;
		}
			// return
		return $this->pi_wrapInBaseClass($content);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php']);
}
?>