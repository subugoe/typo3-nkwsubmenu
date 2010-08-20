<?php
/***************************************************************
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
***************************************************************/
require_once(t3lib_extMgm::extPath('nkwlib') . 'class.tx_nkwlib.php');
/**
 * Plugin 'Infobox' for the 'nkwsubmenu' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwsubmenu
 */
class tx_nkwsubmenu_pi2 extends tx_nkwlib {
	var $prefixId      = 'tx_nkwsubmenu_pi2';
	var $scriptRelPath = 'pi2/class.tx_nkwsubmenu_pi2.php';
	var $extKey        = 'nkwsubmenu';
	var $pi_checkCHash = true;
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		// basics
		$weAreHerePageID = $this->getPageUID();
		 // T3 hack
		$saveATagParams = $GLOBALS['TSFE']->ATagParams;
		$lang = $this->getLanguage();
		$id = $this->checkForAlienContent($weAreHerePageID);
		if (!$id) {
			$id = $weAreHerePageID;
		}
		// get page content
		$pageContent = $this->pageContent($id, $lang);
		if ($pageContent) {
			foreach ($pageContent AS $key => $value) {
				$tmp .= '<li>';
				$tmp .= '<a title="' . $value['header'] . '" href="#c' . $value['uid'] . '">' . $value['header'] . '</a>';
				$tmp .= '</li>';
			}
			if ($tmp) {
				$contentContent .= '<span class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('contentOfThisSite') 
					. ':</span>';
				$contentContent .= '<ul>' . $tmp . '</ul>';
			}
			unset($tmp);
		} else {
			$contentContent = $this->pi_getLL('noContentOfThisSite');
		}
		$contentContent = '<div id="tx-nkwsubmenu-pi2-contentlist">' . $contentContent . '</div>';
		// get children
		$children = $this->pageHasChild($weAreHerePageID);
		if ($children) {
			foreach ($children AS $key => $value) {
				$tmp .= '<li>';
				$tmp .= $this->pi_LinkToPage($this->formatString($value['title']),$value['uid'], '', '');
				$tmp .= '</li>';
			}
			if ($tmp) {
				$contentChildren .= '<span class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('subpages') 
					. ':</span>';
				$contentChildren .= '<ul>' . $tmp . '</ul>';
			}
			unset($tmp);
		} else {
			$contentChildren = $this->pi_getLL('noSubpages');
		}
		$contentChildren = '<div id="tx-nkwsubmenu-pi2-subpagelist">' . $contentChildren . '</div>';
		// keywords
		$contentKeywords = '<div id="tx-nkwsubmenu-pi2-keywordlist">';
		$contentKeywords .= '<span class="tx-nkwsubmenu-pi2-header">' . $this->pi_getLL('keywordsOfThisSite') 
			. ':</span>';
		$contentKeywords .= '<ul>';
		$contentKeywords .= $this->keywordsForPage($weAreHerePageID, $lang, 'infobox');
		$contentKeywords .= '</ul>';
		$contentKeywords .= '</div>';
		// collect
		$content = $contentContent.$contentChildren.$contentKeywords;
		// return
		return $this->pi_wrapInBaseClass($content);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']);
}
?>