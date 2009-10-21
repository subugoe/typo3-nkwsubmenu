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

/**
 * Plugin 'SUB Menu' for the 'nkwsubmenu' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwsubmenu
 */
class tx_nkwsubmenu_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_nkwsubmenu_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_nkwsubmenu_pi1.php';	// Path to this script relative to the extension dir.
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

/*
		echo "<pre>";
		#print_r($GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"]);
		#print_r($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]);
		#print_r($GLOBALS["TSFE"]->page);
		echo "</pre>";
*/
		
		$saveATagParams = $GLOBALS['TSFE']->ATagParams; // T3 hack

		// basics
		$pid = $GLOBALS['TSFE']->id; // page ID
		#$lang = $GLOBALS["TSFE"]->sys_page->sys_language_uid;
		
		// get all pages from DB
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			"*","pages","tx_nkwsubmenu_in_menu = '1' AND hidden != '1' AND deleted = '0'","","sorting ASC","");
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
		{

			// get sub pages
			$res_sub = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				"*",
				"pages",
				"pid = '".$row["uid"]."' AND hidden != '1' AND tx_nkwsubmenu_in_menu != '2' AND deleted = '0'",
				"",
				"sorting ASC",
				"");

			// aggregate sub pages
			unset($content_sub);
			while($row_sub = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_sub))
			{

				if ($pid == $row_sub["uid"]) // this second level menu item is selected
				{
					$gogo = $row_sub["uid"];
					$gogo_pid = $row_sub["pid"];
					$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight"'; // T3 hack
					$content_sub .= "<li>".$this->pi_LinkToPage($row_sub["title"],$row_sub["uid"],"","")."</li>";
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
					
					if (
						$row["tx_nkwsubmenu_picture_follow"] && // eltern seite vererbt ein bild
						!$row_sub["tx_nkwsubmenu_picture_nofollow"] && // und kind mag das auch nicht unterdrücken
						!$row_sub["tx_nkwsubmenu_picture"]
					)
					{
						$GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"] = $row["tx_nkwsubmenu_picture"];
						$GLOBALS["TSFE"]->page["tx_nkwsubmenu_go"] = 1;
					}
					else if ($row_sub["tx_nkwsubmenu_picture"])
					{
						$GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"] = $row_sub["tx_nkwsubmenu_picture"];
					}

				}
				else // just a random second level menu ietm
					$content_sub .= "<li>".$this->pi_LinkToPage($row_sub["title"],$row_sub["uid"],"","")."</li>";

			}

			// format primary menu item
			if ($pid == $row["uid"]) // uh, this menu item is selected
			{
				$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight"'; // T3 hack
				$content_tmp .= "<li>".$this->pi_LinkToPage($row["title"],$row["uid"],"","");
				$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
				
				if ($row["tx_nkwsubmenu_picture"])
				{
					$GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"] = $row["tx_nkwsubmenu_picture"];
					$GLOBALS["TSFE"]->page["tx_nkwsubmenu_go"] = 1;
					#$GLOBALS["TSFE"]->page["tx_nkwsubmenu"] = $row["tx_nkwsubmenu_picture"];
					#$GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["nkwsubmenu"] = serialize(array("test" => $row["tx_nkwsubmenu_picture"]));
					#echo $GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["nkwsubmenu"]["test"];
				}
					#unset($GLOBALS["TSFE"]->test);
			}
			else if ($row["uid"] == $gogo_pid) // is parent of selected page
			{
				$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight_top"'; // T3 hack
				$content_tmp .= "<li>".$this->pi_LinkToPage($row["title"],$row["uid"],"","");
				$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
			}
			else // it's just a random primary level menu item
				$content_tmp .= "<li>".$this->pi_LinkToPage($row["title"],$row["uid"],"","");

			// add sub pages to menu
			if ($content_sub) { // do we have any sub pages?
				if ($pid == $gogo || $pid == $row["uid"]) // this is open
					$content_tmp .= "<ul class='menu_sub go'>".$content_sub."</ul>";
				else // normal state sub menu (closed)
					$content_tmp .= "<ul class='menu_sub'>".$content_sub."</ul>";
				$gogo = FALSE;
			}

			$content_tmp .= "</li>";
		}
		
		$content = "<ul id='menu1' class='menu expand'>".$content_tmp."</ul>"; // wrap everything and go
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php']);

?>