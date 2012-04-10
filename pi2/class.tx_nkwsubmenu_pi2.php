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

/**
 * Plugin 'Infobox' for the 'nkwsubmenu' extension.
 *
 * @author Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @author Ingo Pfennigstorf <pfennigstorf@sub.uni-goettingen.de>
 * @package TYPO3
 * @subpackage tx_nkwsubmenu
 */

class tx_nkwsubmenu_pi2 extends tslib_pibase {

	public $prefixId = 'tx_nkwsubmenu_pi2';
	public $scriptRelPath = 'pi2/class.tx_nkwsubmenu_pi2.php';
	public $extKey = 'nkwsubmenu';
	public $pi_checkCHash = TRUE;

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content The PlugIn content
	 * @param array $conf The PlugIn configuration
	 * @return The content that is displayed on the website
	 */
	public function main($content, $conf) {

		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		// basics
		$weAreHerePageId = $GLOBALS['TSFE']->id;
		$this->lang = $GLOBALS['TSFE']->sys_language_uid;
		// T3 hack
		$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;
		$id = self::checkForAlienContent($weAreHerePageId);
		if (!$id) {
			$id = $weAreHerePageId;
		}

		$contentContent = '';

		// get page content
		$pageContent = self::pageContent($id);
		$contentContent .= '<h6>' . $this->pi_getLL('contentOfThisSite') . '</h6>';
		if ($pageContent) {
			foreach ($pageContent AS $key => $value) {
				if ($value['colPos'] == 0) {
					$tmp .= '<li>';
					$tmp .= '<a title="' . $value['header'] . '" href="#c' . $value['uid'] . '">' . $value['header'] . '</a>';
					$tmp .= '</li>';
				}
			}
			// hook to extend table of contents (add anchors etc.)
			if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC'] as $userFunc) {
					if ($userFunc) {
						t3lib_div::callUserFunction($userFunc, $tmp, $this);
					}
				}
			}
			if ($tmp) {
				$contentContent .= '<ul>' . $tmp . '</ul>';
			}
			unset($tmp);
		} else {
			$contentContent .= '<p>' . $this->pi_getLL('noContentOfThisSite') . '</p>';
		}
		$contentContent = '<div id="tx-nkwsubmenu-pi2-contentlist">' . $contentContent . '</div>';

		// insert pictures in side-menu via hook
		$contentPictures = '<h6>' . $this->pi_getLL('sideBarImages') . '</h6>';
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages'] as $userFunc) {
				if ($userFunc) {
					t3lib_div::callUserFunction($userFunc, $tmp, $this);
				}
			}
			if ($tmp) {
				$contentPictures .= '<div id="tx-nkwsubmenu-pi2-imagelistframe">' . $tmp . '</div>';
				$contentPictures = '<div id="tx-nkwsubmenu-pi2-imagelist">' . $contentPictures . '</div>';
			} else {
				$contentPictures = '';
			}
			unset($tmp);
		} else {
			$contentPictures = '';
		}

		// get children
		$children = $this->pageHasChild($weAreHerePageId);
		$recurs = $this->pi_getPidList($weAreHerePageId, $recursive = 1);
		if ($children || isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendMoreOnThesePages'])) {
			if ($children) {
				foreach ($children AS $key => $value) {
					$tmp .= '<li>' . $i;
					$tmp .= $this->pi_LinkToPage($value['title'], $value['uid'], '', '');
					$tmp .= '</li>';
				}
			}
			// hook to extend MoreOnThesePages (add new sublinks etc.)
			if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendMoreOnThesePages'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendMoreOnThesePages'] as $userFunc) {
					if ($userFunc) {
						t3lib_div::callUserFunction($userFunc, $tmp, $this);
						$tmp = trim($tmp);
					}
				}
			}
			if ($tmp) {
				$contentChildren .= '<h6>' . $this->pi_getLL('subpages') . '</h6>';
				$contentChildren .= '<ul>' . trim($tmp) . '</ul>';
				$contentChildren = '<div id="tx-nkwsubmenu-pi2-subpagelist">' . $contentChildren . '</div>';
			}
			unset($tmp);
		}

		// keywords
		$contentKeywords = '<div id="tx-nkwsubmenu-pi2-keywordlist">';
		$contentKeywords .= '<h6>' . $this->pi_getLL('keywordsOfThisSite') . '</h6>';
		$contentKeywords .= '<ul>';
		$contentKeywords .= self::keywordsForPage($weAreHerePageId, $conf['landing']);
		$contentKeywords .= '</ul>';
		$contentKeywords .= '</div>';

		// collect
		$content = $contentChildren . $contentContent . $contentPictures . $contentKeywords;
		// return
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Checks if a page has child records
	 *
	 * @param int $id
	 * @return mixed
	 */
	protected function pageHasChild($id) {
		$i = 0;
		$arr = array();
		if ($this->lang >= 1) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'pages LEFT JOIN pages_language_overlay ON pages.uid = pages_language_overlay.pid',
					'pages.pid = ' . $id . $this->cObj->enableFields('pages') . ' AND sys_language_uid = ' . $this->lang,
				'',
				'pages.sorting ASC',
				'');
		} else {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'pages',
				'pid = ' . $id . $this->cObj->enableFields('pages'),
				'',
				'sorting ASC',
				'');
		}
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($this->lang > 0) {
				$arr[$i]['uid'] = $row['pid'];
			} else {
				$arr[$i]['uid'] = $row['uid'];
			}
			$arr[$i]['title'] = $row['title'];
			$arr[$i]['tx_nkwsubmenu_in_menu'] = $row['tx_nkwsubmenu_in_menu'];
			$i++;
		}
		if ($i > 0) {
			$return = $arr;
		} else {
			$return = FALSE;
		}
		return $return;
	}

	/**
	 * check if a page uses the content of another page "content_from_pid"
	 *
	 * @param int $id
	 * @return mixed
	 */
	protected function checkForAlienContent($id) {

		$return = '';

		$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, content_from_pid',
			'pages',
				'uid = ' . $id . $this->cObj->enableFields('pages'),
			'',
			'',
			'');
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1)) {
			$contentFromPid = $row1['content_from_pid'];
		}
		if ($contentFromPid) {
			$return = $contentFromPid;
		} else {
			$return = FALSE;
		}
		return $return;
	}

	/**
	 * Get Keywords for a page
	 *
	 * @param int $id
	 * @param boolean $mode
	 * @param boolean $landingpage
	 * @return string
	 */
	protected function keywordsForPage($id, $landingpage = FALSE) {

		$cObj = t3lib_div::makeInstance('tslib_cObj');

		$pageInfo = tx_nkwlib::pageInfo($id, $GLOBALS['TSFE']->sys_language_uid);
		if (!empty($pageInfo['tx_nkwkeywords_keywords'])) {
				$tmp = explode(',', $pageInfo['tx_nkwkeywords_keywords']);
				foreach ($tmp AS $key => $value) {
					$value = intval($value);

					$select = '*';
					$table = 'tx_nkwkeywords_keywords';
					$where = '(sys_language_uid IN (-1,0) OR (sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid . ')) AND uid = ' . $value;
					$where .= $GLOBALS['TSFE']->sys_page->enableFields($table);
					$order = '';
					$group = '';
					$limit = '';
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $group, $order, $limit);

					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

						if (is_array($row) && $row['sys_language_uid'] != $GLOBALS['TSFE']->sys_language_content && $GLOBALS['TSFE']->sys_language_contentOL) {
							$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($table, $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
						}
						if ($row) {
							$link_uid = ($row['_LOCALIZED_UID']) ? $row['_LOCALIZED_UID'] : $row['uid'];
						}
						$str .= '<li>';

						$cObj->typoLink(
							$row['title' . $langString],
							array(
								'parameter' => $link_uid,
								'useCacheHash' => TRUE,
								'additionalParams' => '&tx_nkwkeywords[id]=' . $value
							)
						);
						if ($this->lang === 0) {
							$langString = '_de';
							} else {
								$langString = '_en';
								}
						$str .= '<a title="' . $row['title' . $langString] . '" href="' . $cObj->lastTypoLinkUrl . '">' . $row['title' . $langString] . '</a>';
						$str .= '</li>';
					}
				}
		}
		return $str;
	}

	/**
	 * Returns an Array Containing the UID and header field of content elements
	 * of a page
	 * If no content element it returns false
	 *
	 * @param int $id
	 * @return mixed
	 */
	protected function pageContent($id) {
		$i = 0;
		$arr = array();
		$return = '';

		$id = intval($id);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, header, colPos',
			'tt_content',
			'pid = ' . $id . ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid . $GLOBALS['TSFE']->sys_page->enableFields('tt_content'),
			'',
			'sorting ASC',
			''
			);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$arr[$i]['uid'] = $row['uid'];
			$arr[$i]['header'] = $row['header'];
			$arr[$i]['colPos'] = $row['colPos'];
			$i++;
		}
		if (count($arr) > 0) {
			$return = $arr;
		} else {
			$return = FALSE;
		}
		return $return;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']);
}
?>