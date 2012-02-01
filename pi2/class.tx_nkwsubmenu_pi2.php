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
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @author Ingo Pfennigstorf <pfennigstorf@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwsubmenu
 */

require_once(t3lib_extMgm::extPath('nkwlib', 'class.tx_nkwlib.php'));

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
			// $this->pi_USER_INT_obj = 1;
		$this->pi_loadLL();
			// basics
		$weAreHerePageId = $GLOBALS['TSFE']->id;
			// T3 hack
		$saveAnchorTagParams = $GLOBALS['TSFE']->ATagParams;
		$lang = tx_nkwlib::getLanguage();
		$id = tx_nkwlib::checkForAlienContent($weAreHerePageId);
		if (!$id) {
			$id = $weAreHerePageId;
		}

		$contentContent = '';

			// get page content
		$pageContent = tx_nkwlib::pageContent($id, $lang);
		$contentContent .= '<div class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('contentOfThisSite') . '</div>';
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
		$contentPictures = '<div class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('sideBarImages') . '</div>';
				if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages'] as $userFunc) {
						if ($userFunc) {
							t3lib_div::callUserFunction($userFunc, $tmp, $this);
						}
					}
					if ($tmp) {
						$contentPictures .= '<div id="tx-nkwsubmenu-pi2-imagelistframe">' . $tmp . '</div>';
						$contentPictures  = '<div id="tx-nkwsubmenu-pi2-imagelist">' . $contentPictures . '</div>';
					}   else	{
						$contentPictures = '';
					}
					unset($tmp);
				}   else	{
					$contentPictures = '';
				}

			// get children
		$children = tx_nkwlib::pageHasChild($weAreHerePageId, $lang);
		$recurs = 	$this->pi_getPidList($weAreHerePageId,$recursive=1);
		if ($children || isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendMoreOnThesePages'])) {
			if($children)	{
				foreach ($children AS $key => $value) {
						$tmp .= '<li>' . $i;
						$tmp .= $this->pi_LinkToPage(tx_nkwlib::formatString($value['title']), $value['uid'], '', '');
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
				$contentChildren .= '<div class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('subpages') . '</div>';
				$contentChildren .= '<ul>' . trim($tmp) . '</ul>';
				$contentChildren  = '<div id="tx-nkwsubmenu-pi2-subpagelist">' . $contentChildren . '</div>';
			}
			unset($tmp);
		}

			// keywords
		$contentKeywords = '<div id="tx-nkwsubmenu-pi2-keywordlist">';
		$contentKeywords .= '<div class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('keywordsOfThisSite') . '</div>';
		$contentKeywords .= '<ul>';
		$contentKeywords .= tx_nkwlib::keywordsForPage($weAreHerePageId, $lang, 'infobox', $conf['landing']);
		$contentKeywords .= '</ul>';
		$contentKeywords .= '</div>';

			// collect
		$content = $contentChildren . $contentContent . $contentPictures .  $contentKeywords;
			// return
		return $this->pi_wrapInBaseClass($content);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']);
}
?>