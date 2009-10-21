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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('nkwlib')."class.tx_nkwlib.php");

/**
 * Plugin 'Keyword List' for the 'nkwsubmenu' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwsubmenu
 */
class tx_nkwsubmenu_pi3 extends tx_nkwlib {
	var $prefixId      = 'tx_nkwsubmenu_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_nkwsubmenu_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'nkwsubmenu';	// The extension key.
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
		$weAreHerePageID = $GLOBALS['TSFE']->id; // page ID
		$saveATagParams = $GLOBALS['TSFE']->ATagParams; // T3 hack
		$lang = $GLOBALS["TSFE"]->sys_page->sys_language_uid;
		$queryAdd = " AND hidden = '0' AND deleted = '0'";
		$getKeyword = addslashes(htmlspecialchars(trim($this->piVars["keyword"])));


		if ($getKeyword)
		{
			$content = "<h2>".$this->pi_getLL("selectedKeyword").": ".$getKeyword."</h2>";
			$queryWhat = "title, uid, keywords";
			$queryWhere = "(FIND_IN_SET(' ".$getKeyword."',keywords) OR FIND_IN_SET('".$getKeyword."',keywords)) ";
			$querySort = "title ASC";
			if ($lang > 0)
				$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($queryWhat,"pages_language_overlay",$queryWhere." AND sys_language_uid = '".$lang."'".$queryAdd,"",$querySort,"");
			else
				$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($queryWhat,"pages",$queryWhere.$queryAdd,"",$querySort,"");
			while($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1))
				$tmp .= "<li>".$this->pi_LinkToPage($row1["title"],$row1["uid"],"","")."</li>";
			if ($tmp)
				$content .= "<p class='hint'>(".$this->pi_getLL("hint").")</p><ul>".$tmp."</ul>";
			else
				$content .= "<p>".$this->pi_getLL("noHits").$getKeyword."</p>";
		}
		else
		{
			// get all keywords
			if ($lang > 0)
				$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","pages_language_overlay","keywords != '' AND sys_language_uid = '".$lang."'".$queryAdd,"","","");
			else
				$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","pages","keywords != ''".$queryAdd,"","","");
			while($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1))
				$keywords .= ", ".$row1["keywords"];
	
	
			// make array, remove duplicates and sort alpha
			$keywords = explode(", ", $keywords);
			$keywords = array_unique($keywords);
			asort($keywords);
	
	
			// arrange list
			$tmpLetterArr = array();
			foreach ($keywords AS $key => $value)
			{
				if ($value)
				{
					$letter = strtoupper($value{0});
					// den Buchstaben haben wir noch nicht
					if (!in_array($letter,$tmpLetterArr))
					{
						array_push($tmpLetterArr, $letter);
						if (!$ulStart)
							$ulStart = TRUE;
						else
							$tmp .= "</ul></li>";
						$tmp .= "<li class='liAsHeader'><a name='".$letter."'></a><strong>".$letter."</strong><ul>";
					}
					$tmp .= "<li>".$this->pi_LinkToPage($value,$weAreHerePageID,"",array($this->prefixId."[keyword]" => $value))."</li>";
				}
			}
			$tmp = $tmp;
	
	
			// alpha list
			$keywordsAlphaList = $this->alphaListFromArray($keywords);
			foreach($keywordsAlphaList AS $key => $value)
				$tmpKeywordsAlphaList .= "<a href='#".$value."'>".$value."</a> | ";
			$tmpKeywordsAlphaList = substr($tmpKeywordsAlphaList, 0, -3);
	
	
			// output
			if ($tmp)
				$content = "<ul class='resetUlMargin'>".$tmpKeywordsAlphaList."</ul>";
				$content .= "<ul class='resetUlMargin'>".$tmp."</ul>";
		}

		// return
		return $this->pi_wrapInBaseClass($content);


	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']);

?>