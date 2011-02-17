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
 * Plugin 'SUB Menu' for the 'nkwsubmenu' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwsubmenu
 * $Id$
 */
class tx_nkwsubmenu_pi1 extends tx_nkwlib {
	var $prefixId      = 'tx_nkwsubmenu_pi1';
	var $scriptRelPath = 'pi1/class.tx_nkwsubmenu_pi1.php';
	var $extKey        = 'nkwsubmenu';
	var $pi_checkCHash = true;
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf) {
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		// basics
		$weAreHerePageID = $GLOBALS['TSFE']->id; // page ID
		$saveATagParams = $GLOBALS['TSFE']->ATagParams; // T3 hack
		$lang = $this->getLanguage();
		$knot = $this->knotID($weAreHerePageID);
		$pageInfo = $this->pageInfo($weAreHerePageID, $lang);
		// FIRST LEVEL
		// query
		// get all pages which are menu items (tx_nkwsubmenu_in_menu = '1') or those who are not, 
		// 		but contain a knot (tx_nkwsubmenu_in_menu = '3')
		if ($knot) {
			$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'pages',
				'uid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($knot, 'pages') 
					. ' AND hidden != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(1, 'pages') 
					// . ' AND hidden != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages') 
					. ' AND deleted = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages'),
				'',
				'sorting ASC',
				'');
		} else {
			$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				#"*","pages","tx_nkwsubmenu_in_menu = '1' AND hidden != '1' AND deleted = '0'","","sorting ASC","");
				'*',
				'pages',
				'(tx_nkwsubmenu_in_menu = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(1, 'pages') 
					. ' OR tx_nkwsubmenu_in_menu = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(3, 'pages') 
					. ') AND hidden != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(1, 'pages') 
					. ' AND deleted = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages'),
				'',
				'sorting ASC',
				'');
		}
		// helper
		$i1 = 0;
		// cycle
		while($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1)) {
			$pages[$i1]['uid'] = $row1['uid'];
			$pages[$i1]['pid'] = $row1['pid'];
			$pages[$i1]['title'] = $this->formatString($row1['title']);
			// we don't use the default language, so get the alternative page title
			if ($lang > 0) {
				$pages[$i1]['title'] = $this->formatString($this->getPageTitle($row1['uid'], $lang));
			}
			$pages[$i1]['tx_nkwsubmenu_in_menu'] = $row1['tx_nkwsubmenu_in_menu'];
			$pages[$i1]['tx_nkwsubmenu_picture'] = $row1['tx_nkwsubmenu_picture'];
			$pages[$i1]['tx_nkwsubmenu_knotheader'] = $row1['tx_nkwsubmenu_knotheader'];
			$pages[$i1]['tx_nkwsubmenu_picture_follow'] = $row1['tx_nkwsubmenu_picture_follow'];
			$pages[$i1]['tx_nkwsubmenu_usecontent'] = $row1['tx_nkwsubmenu_usecontent'];
			// check if knot
			if ($row1['tx_nkwsubmenu_knot']) {
				$pages[$i1]['isKnot'] = 1;
			}
			// check if selected
			if ($row1['uid'] == $weAreHerePageID) {
				$pages[$i1]['selected'] = 1;
			}
			if ($pages[$i1]['tx_nkwsubmenu_usecontent']) {
				// query
				$resContent = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*', 
					'tt_content', 
					'pid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($row1['uid'], 'tt_content') 
						. ' AND hidden = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'tt_content') 
						. ' AND deleted = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'tt_content') 
						. ' AND sys_language_uid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($lang, 'tt_content'), 
					'', 
					'sorting ASC',
					'');
				$i = 0;
				while($rowContent = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resContent)) {
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
				'pid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($row1['uid'], 'pages') 
					. ' AND tx_nkwsubmenu_in_menu != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(2, 'pages') 
					. ' AND hidden = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages') 
					. ' AND deleted = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages'), 
				'', 
				'sorting ASC', 
				'');
			// helper
			$i2 = 0;
			// cycle
			while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
				$pages[$i1]['child'][$i2]['uid'] = $row2['uid'];
				$pages[$i1]['child'][$i2]['title'] = $this->formatString($row2['title']);
				if ($lang > 0) {
					$pages[$i1]['child'][$i2]['title'] = $this->formatString($this->getPageTitle($row2['uid'], $lang));
				}
				$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture'] = $row2['tx_nkwsubmenu_picture'];
				$pages[$i1]["child"][$i2]["tx_nkwsubmenu_knotheader"] = $row2['tx_nkwsubmenu_knotheader'];
				$pages[$i1]["child"][$i2]['tx_nkwsubmenu_picture_follow'] = $row2['tx_nkwsubmenu_picture_follow'];
				$pages[$i1]["child"][$i2]['tx_nkwsubmenu_picture_nofollow'] = $row2['tx_nkwsubmenu_picture_nofollow'];
				$pages[$i1]["hasChild"] = 1;
				// pictures
				if (!$row2['tx_nkwsubmenu_picture'] 
					&& !$row2['tx_nkwsubmenu_picture_nofollow'] 
					&& $row1['tx_nkwsubmenu_picture'] 
					&& $row1['tx_nkwsubmenu_picture_follow']) {
					$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture'] = $row1['tx_nkwsubmenu_picture'];
					$pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture_follow'] = 1;
				}
				// knot
				if ($row2['tx_nkwsubmenu_knot']) {
					$pages[$i1]['child'][$i2]['isKnot'] = 1;
					$pages[$i1]['hasKnot'] = 1;
				}
				// selected
				if ($row2['uid'] == $weAreHerePageID) {
					$pages[$i1]['selected'] = 2;
					$pages[$i1]['child'][$i2]['selected'] = 1;
				}
				// check if second level item is within knot
				if ($row1['tx_nkwsubmenu_knot']) {
					$pages[$i1]['child'][$i2]['inKnot'] = 1;
				}
				if ($row2['uid'] == $weAreHerePageID) {
					$pages[$i1]['hasActiveKnot'] = 1;
				}
				// THIRD LEVEL
				// query
				$res3 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*', 
					'pages', 
					'pid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($row2['uid'], 'pages') 
						. ' AND tx_nkwsubmenu_in_menu != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(2, 'pages')
						. ' AND hidden = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages') 
						. ' AND deleted = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages'), 
					'', 
					'sorting ASC', 
					'');
				// helper
				$i3 = 0;
				// cycle
				while($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res3)) {
					$pages[$i1]['child'][$i2]['child'][$i3]['uid'] = $row3['uid'];
					$pages[$i1]['child'][$i2]['child'][$i3]['title'] = $this->formatString($row3['title']);
					if ($lang > 0) {
						$pages[$i1]['child'][$i2]['child'][$i3]['title'] = $this->formatString(
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
					// knot
					if ($row3['tx_nkwsubmenu_knot']) {
						$pages[$i1]['child'][$i2]['child'][$i3]['isKnot'] = 1;
						$pages[$i1]['child'][$i2]['hasKnot'] = 1;
						$pages[$i1]['hasKnot'] = 1;
					}
					if ($row3['uid'] == $weAreHerePageID) {
						$pages[$i1]['child'][$i2]['hasActiveKnot'] = 1;
						$pages[$i1]['hasActiveKnot'] = 1;
					}
					// selected
					if ($row3['uid'] == $weAreHerePageID) {
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
						'pid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($pages[$i1]['child'][$i2]['child'][$i3]['uid'], 'pages') 
							. ' AND tx_nkwsubmenu_in_menu != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(2, 'pages')
							. ' AND hidden = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages') 
							. ' AND deleted = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(0, 'pages'), 
						'', 
						'sorting ASC', 
						'');
					while($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res4)) {
						$pages[$i1]['child'][$i2]['child'][$i3]['child'][$i4]['uid'] = $row4['uid'];
						$pages[$i1]['child'][$i2]['child'][$i3]['child'][$i4]['title'] = $row4['title'];
						if ($weAreHerePageID == $row4['uid']) {
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
		for ($i = 0;$i < $pagesSize; $i++) {
			// FIRST
			if ($pages[$i]['selected'] == 1){
				$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i]['tx_nkwsubmenu_picture'];
			} elseif ($pages[$i]['selected'] >= 2) { // set picture
				if ($pages[$i]['hasChild']) {
					// SECOND
					for ($ii = 0;$ii < sizeof($pages[$i]['child']); $ii++) {
						if ($pages[$i]['child'][$ii]['selected'] == 1) {
							$GLOBALS["TSFE"]->page['tx_nkwsubmenu_picture'] = $pages[$i]['child'][$ii]['tx_nkwsubmenu_picture']; // set picture
						} elseif($pages[$i]['child'][$ii]['selected'] == 2) {
							// THIRD
							for ($iii = 0;$iii < sizeof($pages[$i]['child'][$ii]['child']); $iii++) {
								if($pages[$i]['child'][$ii]['child'][$iii]['selected'] == 1) {
									$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i]['child'][$ii]['child'][$iii]['tx_nkwsubmenu_picture']; // set picture
								}
							}
						}
					}
				}
			}
		}
		// set banner picture
		// if $knot
		if ($knot) {
			#$this->dprint("1. ".$pagesSize."-".sizeof($pages));
			// remove everything from first level that has no active knot
			for ($i = 0;$i < $pagesSize; $i++) {
				if ($pages[$i]['isKnot'] || $pages[$i]['hasActiveKnot']) {
					$tmp = $pages[$i]; // tmp save array index with active knot
					unset($pages); // reset pages array
					$pages[0] = $tmp; // set pages array with active knot index only
				}
			}
			#$this->dprint("2. ".$pagesSize."-".sizeof($pages));
			#$this->dprint($pages);
			#$pagesSize = sizeof($pages);
			// remove everything from second level that has no active knot
			#for ($i = 0;$i < sizeof($pages); $i++)
			#{
				#for ($ii = 0; $ii < sizeof($pages[$i]); $ii++)
			for ($ii = 0; $ii < sizeof($pages[0]['child']); $ii++) {
				if ($pages[0]['child'][$ii]['isKnot'] && $pages[0]['child'][$ii]['selected']) {
					$tmp = $pages[0]['child'][$ii]; // tmp save array index with active knot
					unset($pages[0]['child']); // reset pages array
					$pages[0] = $tmp; // set pages array with active knot index only
				}
			}
/*
				for ($ii = 0; $ii < sizeof($pages[0]["child"]); $ii++)
				{
					if ($pages[0]["child"][$ii]["isKnot"] || $pages[0]["child"][$ii]["hasActiveKnot"])
					{
						$tmp = $pages[0]["child"][$ii]; // tmp save array index with active knot
						unset($pages[0]["child"]); // reset pages array
						$pages[0] = $tmp; // set pages array with active knot index only
					}
				}
*/
			#}
			// remove everything from third level that has no active knot
			for ($i = 0;$i < sizeof($pages[0]['child']); $i++) {
				if ($pages[0]['child'][$i]['isKnot']) {
					$tmp = $pages[0]['child'][$i]; // tmp save array index with active knot
					unset($pages[0]['child']); // reset pages array
					$pages[0] = $tmp; // set pages array with active knot index only
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
			#$menuContent .= "<li class='tx-nkwsubmenu-pi1-l1'>";
			if ($pages[$i1]['selected'] == 1) {
				$menuContent .= '<li class="tx-nkwsubmenu-pi1-selected">';
			} else {
				$menuContent .= "\t\t<li>";
			}
			// $menuContent .= '<span>';
			// format link
			if ($pages[$i1]['selected'] == 1) {
				if ($pages[$i1]['hasChild'] && !$pages[$i1]['isKnot']) {
					$menuContent .= '<a class="tx-nkwsubmenu-pi1-highlight tx-nkwsubmenu-pi1-trigger">' 
						. $pages[$i1]['title'] . '</a>';
				} else {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['title'] 
						. '" class="tx-nkwsubmenu-pi1-highlight tx-nkwsubmenu-pi1-trigger"'; // T3 hack
					// $menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '') . '</span>';
					$menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
				}
				// set banner picture
				#$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i1]['tx_nkwsubmenu_picture'];
			} else if ($pages[$i1]['selected'] == 2 || $pages[$i1]['selected'] == 3) {
				if ($pages[$i1]['hasChild']  && !$pages[$i1]['isKnot']) {
					$menuContent .= '<a title="' . $pages[$i1]['title'] 
						. '" class="tx-nkwsubmenu-pi1-highlight-parent tx-nkwsubmenu-pi1-trigger">' 
						. $pages[$i1]['title'] . '</a>';
				} else {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['title'] 
						. '" class="tx-nkwsubmenu-pi1-highlight-parent tx-nkwsubmenu-pi1-trigger"'; // T3 hack
					$menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
				}
			} else {
				if ($pages[$i1]['hasChild']) {
					$menuContent .= '<a title="' . $pages[$i1]['title'] . '" class="tx-nkwsubmenu-pi1-trigger">' 
						. $pages[$i1]['title'] . '</a>';
				} else {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['title'] 
						. '" class="tx-nkwsubmenu-pi1-trigger"'; // T3 hack
					$menuContent .= $this->pi_LinkToPage($pages[$i1]['title'], $pages[$i1]['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
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
					$saveATagParams = $GLOBALS['TSFE']->ATagParams;
					$GLOBALS['TSFE']->ATagParams = 'title="' . $value['header'] . '"';
					$menuContent .= $this->pi_LinkToPage(
						$value['header'], $pages[$i1]['uid'] . '#c' . $value['uid'], '', '');
					$GLOBALS['TSFE']->ATagParams = $saveATagParams;
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
						#$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i1]['child'][$i2]['tx_nkwsubmenu_picture'];
					} else {
						$menuContent .= "\t\t<li>";
					}
					// format link
					if ($pages[$i1]['child'][$i2]['selected'] == 1) {
						$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['title'] 
							. '" class="tx-nkwsubmenu-pi1-highlight"'; // T3 hack
						$menuContent .= $this->pi_LinkToPage(
							$pages[$i1]['child'][$i2]['title'], 
							$pages[$i1]['child'][$i2]['uid'], 
							'', 
							'');
						$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
					} else if ($pages[$i1]['child'][$i2]['selected'] == 2) {
						$saveATagParams = $GLOBALS['TSFE']->ATagParams;
						$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['title'] . '"';
						$menuContent .= $this->pi_LinkToPage(
							$pages[$i1]['child'][$i2]['title'], 
							$pages[$i1]['child'][$i2]['uid'], 
							'', 
							'');
						$GLOBALS['TSFE']->ATagParams = $saveATagParams;
					} else {
						$saveATagParams = $GLOBALS['TSFE']->ATagParams;
						$GLOBALS['TSFE']->ATagParams = 'title="' . $pages[$i1]['child'][$i2]['title'] . '"';
						$menuContent .= $this->pi_LinkToPage(
							$pages[$i1]['child'][$i2]['title'], 
							$pages[$i1]['child'][$i2]['uid'], 
							'', 
							'');
						$GLOBALS['TSFE']->ATagParams = $saveATagParams;
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
								$GLOBALS['TSFE']->ATagParams = 'title="' 
									. $pages[$i1]['child'][$i2]['child'][$i3]['title'] 
									. '" class="tx-nkwsubmenu-pi1-highlight"'; // T3 hack
								$menuContent .= $this->pi_LinkToPage(
									$pages[$i1]['child'][$i2]['child'][$i3]['title'], 
									$pages[$i1]['child'][$i2]['child'][$i3]['uid'], 
									'', 
									'');
								$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
								// set banner picture
								#$GLOBALS['TSFE']->page['tx_nkwsubmenu_picture'] = $pages[$i1]['child'][$i2]['child'][$i3]['tx_nkwsubmenu_picture'];								
							} else {
								$menuContent .= "\t\t\t\t<li>";
								$saveATagParams = $GLOBALS['TSFE']->ATagParams;
								$GLOBALS['TSFE']->ATagParams = 'title="' 
									. $pages[$i1]['child'][$i2]['child'][$i3]['title'] . '"';
								$menuContent .= $this->pi_LinkToPage(
									$pages[$i1]['child'][$i2]['child'][$i3]['title'], 
									$pages[$i1]['child'][$i2]['child'][$i3]['uid'], 
									'', 
									'');
								$GLOBALS['TSFE']->ATagParams = $saveATagParams;
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
		$content = '<ul id="menu1" class="tx-nkwsubmenu-pi1-l1 expand">' . $menuContent . $content_tmp . '</ul>';
		// show a back to startpage link if in knot
		if ($knot) {
			$pageInfo = $this->pageInfo(1, $lang);
			$GLOBALS['TSFE']->ATagParams = 'title="' . $pageInfo['title'] . '" class="tx-nkwsubmenu-pi1-trigger"'; // T3 hack
			$content = '<ul><li>' . $this->pi_LinkToPage($pageInfo['title'], 1, '', '') . '</li></ul>' . $content;
			$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
		}
		// return
		return $this->pi_wrapInBaseClass($content);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php']);
}
?>