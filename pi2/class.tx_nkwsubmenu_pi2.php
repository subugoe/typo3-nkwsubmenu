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

			// keywords

		$keywords = self::keywordsForPage($weAreHerePageId, $this->conf['landing']);

		if ($keywords) {
			$contentKeywords = '<div id="tx-nkwsubmenu-pi2-keywordlist">';
			$contentKeywords .= '<h6>' . $this->pi_getLL('keywordsOfThisSite') . '</h6>';
			$contentKeywords .= '<ul>';
			$contentKeywords .= $keywords;
			$contentKeywords .= '</ul>';
			$contentKeywords .= '</div>';
		} else {
			$contentKeywords = '';
		}
			// collect
		$content = $contentContent . $contentPictures . $contentKeywords;
			// return
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * check if a page uses the content of another page "content_from_pid"
	 *
	 * @param int $id
	 * @return mixed
	 */
	protected function checkForAlienContent($id) {

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
	 * @param boolean $landingpage
	 * @return string
	 */
	protected function keywordsForPage($id, $landingpage = FALSE) {

		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$pageInfo = t3lib_BEfunc::getRecord('pages', $id);
		$str = NULL;

		if ($pageInfo['keywords']) {

			$select = '*';
			$local_table = 'pages';
			$mm_table = 'tx_nkwkeywords_pages_keywords_mm';
			$foreign_table = 'tx_nkwkeywords_domain_model_keywords';
			$whereClause = ' AND ' . $local_table . '.uid = ' . $id;
			$whereClause .= $GLOBALS['TSFE']->sys_page->enableFields($local_table);

			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query (
			 	$select,
				$local_table,
				$mm_table,
				$foreign_table,
				$whereClause
			);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

				$str .= '<li>';
				$cObj->typoLink(
					$row['title'],
					array(
						'parameter' => $landingpage,
						'useCacheHash' => TRUE,
						'additionalParams' => '&tx_nkwkeywords_keyword[keyword]=' . $row['uid']
					)
				);
				$str .= '<a title="' . $row['title'] . '" href="' . $cObj->lastTypoLinkUrl . '">' . $row['title'] . '</a>';
				$str .= '</li>';
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