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
 * Plugin 'SUB Menu' for the 'nkwsubmenu' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwsubmenu
 */
class tx_nkwsubmenu_pi1 extends tx_nkwlib {
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

		#echo "<pre>";
		#print_r($GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"]);
		#print_r($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]);
		#print_r($GLOBALS["TSFE"]->page);
		#echo "</pre>";

		// basics
		$weAreHerePageID = $GLOBALS['TSFE']->id; // page ID
		$saveATagParams = $GLOBALS['TSFE']->ATagParams; // T3 hack
		$lang = $this->getLanguage();
		$knot = $this->knotID($weAreHerePageID);
		$pageInfo = $this->pageInfo($weAreHerePageID, $lang);

		// FIRST LEVEL

		// query
		// get all pages that are menu items (tx_nkwsubmenu_in_menu = '1') or those who are not, but contain a knot (tx_nkwsubmenu_in_menu = '3')
		$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			#"*","pages","tx_nkwsubmenu_in_menu = '1' AND hidden != '1' AND deleted = '0'","","sorting ASC","");
			"*","pages","(tx_nkwsubmenu_in_menu = '1' OR tx_nkwsubmenu_in_menu = '3') AND hidden != '1' AND deleted = '0'","","sorting ASC",""); #2009-12-01

		$i1 = 0; // helper

		// cycle
		while($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1))
		{

			$pages[$i1]["uid"] = $row1["uid"];
			$pages[$i1]["pid"] = $row1["pid"];
			$pages[$i1]["title"] = $this->formatString($row1["title"]);
			$pages[$i1]["tx_nkwsubmenu_in_menu"] = $row1["tx_nkwsubmenu_in_menu"]; #2009-12-01
			$pages[$i1]["tx_nkwsubmenu_picture"] = $row1["tx_nkwsubmenu_picture"];
			$pages[$i1]["tx_nkwsubmenu_knotheader"] = $row1["tx_nkwsubmenu_knotheader"];
			$pages[$i1]["tx_nkwsubmenu_picture_follow"] = $row1["tx_nkwsubmenu_picture_follow"];
			$pages[$i1]["tx_nkwsubmenu_usecontent"] = $row1["tx_nkwsubmenu_usecontent"];

			// check if knot
			if ($row1["tx_nkwsubmenu_knot"])
				$pages[$i1]["isKnot"] = 1;

			// check if selected
			if ($row1["uid"] == $weAreHerePageID)
				$pages[$i1]["selected"] = 1;
			
			
			if ($pages[$i1]["tx_nkwsubmenu_usecontent"])
			{
				// query
				$resContent = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					"*","tt_content","pid = '".$row1["uid"]."' AND hidden = '0' AND deleted = '0' AND sys_language_uid = '".$lang."'","","sorting ASC","");
				
				$i = 0;
				while($rowContent = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resContent))
				{
					$arrContent[$i]["header"] = $rowContent["header"];
					$arrContent[$i]["uid"] = $rowContent["uid"];
					$i++;
				}
				$pages[$i1]["content"] = $arrContent;
			}
			
			#debug($pages);
			
			// SECOND LEVEL

			// query
			$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				"*","pages","pid = '".$row1["uid"]."' AND tx_nkwsubmenu_in_menu != '2' AND hidden = '0' AND deleted = '0'","","sorting ASC","");

			$i2 = 0; // helper

			// cycle
			while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))
			{

				$pages[$i1]["child"][$i2]["uid"] = $row2["uid"];
				$pages[$i1]["child"][$i2]["title"] = $this->formatString($row2["title"]);
				$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture"] = $row2["tx_nkwsubmenu_picture"];
				$pages[$i1]["child"][$i2]["tx_nkwsubmenu_knotheader"] = $row2["tx_nkwsubmenu_knotheader"];
				$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture_follow"] = $row2["tx_nkwsubmenu_picture_follow"];
				$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture_nofollow"] = $row2["tx_nkwsubmenu_picture_nofollow"];
				$pages[$i1]["hasChild"] = 1;

				// pictures
				if (
					!$row2["tx_nkwsubmenu_picture"] && 
					!$row2["tx_nkwsubmenu_picture_nofollow"] && 
					$row1["tx_nkwsubmenu_picture"] && 
					$row1["tx_nkwsubmenu_picture_follow"]
				)
				{
					$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture"] = $row1["tx_nkwsubmenu_picture"];
					$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture_follow"] = 1;
				}

				// knot
				if ($row2["tx_nkwsubmenu_knot"])
				{
					$pages[$i1]["child"][$i2]["isKnot"] = 1;
					$pages[$i1]["hasKnot"] = 1;
				}

				// selected
				if ($row2["uid"] == $weAreHerePageID)
				{
					$pages[$i1]["selected"] = 2;
					$pages[$i1]["child"][$i2]["selected"] = 1;
				}

				// check if second level item is within knot
				if ($row1["tx_nkwsubmenu_knot"])
					$pages[$i1]["child"][$i2]["inKnot"] = 1;
				if ($row2["uid"] == $weAreHerePageID)
					$pages[$i1]["hasActiveKnot"] = 1;


				// THIRD LEVEL
				
				// query
				$res3 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					"*","pages","pid = '".$row2["uid"]."' AND tx_nkwsubmenu_in_menu != '2' AND hidden = '0' AND deleted = '0'","","sorting ASC","");

				$i3 = 0; // helper

				// cycle
				while($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res3))
				{

					$pages[$i1]["child"][$i2]["child"][$i3]["uid"] = $row3["uid"];
					$pages[$i1]["child"][$i2]["child"][$i3]["title"] = $this->formatString($row3["title"]);
					$pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_picture"] = $row3["tx_nkwsubmenu_picture"];
					$pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_knotheader"] = $row3["tx_nkwsubmenu_knotheader"];
					$pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_picture_follow"] = $row3["tx_nkwsubmenu_picture_follow"];
					$pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_picture_nofollow"] = $row3["tx_nkwsubmenu_picture_nofollow"];
					$pages[$i1]["child"][$i2]["hasChild"] = 1;

					// pictures
					if (
						!$row3["tx_nkwsubmenu_picture"] && 
						!$row3["tx_nkwsubmenu_picture_nofollow"] && 
						$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture"] && 
						$pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture_follow"]
					)
					{
						$pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_picture"] = $pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture"];
						$pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_picture_follow"] = 1;
					}

					// knot
					if ($row3["tx_nkwsubmenu_knot"])
					{
						$pages[$i1]["child"][$i2]["child"][$i3]["isKnot"] = 1;
						$pages[$i1]["child"][$i2]["hasKnot"] = 1;
						$pages[$i1]["hasKnot"] = 1;
					}
					if ($row3["uid"] == $weAreHerePageID)
					{
						$pages[$i1]["child"][$i2]["hasActiveKnot"] = 1;
						$pages[$i1]["hasActiveKnot"] = 1;
					}

					// selected
					if ($row3["uid"] == $weAreHerePageID)
					{
						$pages[$i1]["selected"] = 3;
						$pages[$i1]["child"][$i2]["selected"] = 2;
						$pages[$i1]["child"][$i2]["child"][$i3]["selected"] = 1;
					}

					// check if third level item is within knot
					if ($pages[$i1]["child"][$i2]["isKnot"] || $pages[$i1]["child"][$i2]["inKnot"])
						$pages[$i1]["child"][$i2]["child"][$i3]["inKnot"] = 1;

					$i3++; // helper

				}

				$i2++; // helper

			}

			$i1++; // helper

		}

		$pagesSize = $i1; // helper
		
		#debug($pages);

		if ($knot) // if $knot
		{

			// remove everything from first level that has no active knot
			for ($i=0;$i<$pagesSize;$i++)
			{
				if ($pages[$i]["isKnot"] || $pages[$i]["hasActiveKnot"])
				{
					$tmp = $pages[$i]; // tmp save array index with active knot
					unset($pages); // reset pages array
					$pages[0] = $tmp; // set pages array with active knot index only
				}
			}

			// remove everything from second level that has no active knot
			for ($i=0;$i<sizeof($pages[0]["child"]);$i++)
			{
				if ($pages[0]["child"][$i]["isKnot"] || $pages[0]["child"][$i]["hasActiveKnot"])
				{
					$tmp = $pages[0]["child"][$i]; // tmp save array index with active knot
					unset($pages[0]["child"]); // reset pages array
					$pages[0] = $tmp; // set pages array with active knot index only
				}
			}

			// remove everything from third level that has no active knot
			for ($i=0;$i<sizeof($pages[0]["child"]);$i++)
			{
				if ($pages[0]["child"][$i]["isKnot"])
				{
					$tmp = $pages[0]["child"][$i]; // tmp save array index with active knot
					unset($pages[0]["child"]); // reset pages array
					$pages[0] = $tmp; // set pages array with active knot index only
				}
			}

		}

		// remove pages who contain knots (not active), but don't show up in the menu
		if (!$knot)
		{
			for ($i=0;$i<$pagesSize;$i++)
				if ($pages[$i]["tx_nkwsubmenu_in_menu"] == 1)
					$tmp[] = $pages[$i];
			$pages = $tmp;
		}

		// FIRST
		for ($i1=0;$i1<$pagesSize;$i1++)
		{
			
			$menuContent .= "<li>";
			
			#$this->dprint($pages[$i1]);
			
			// format link
			if ($pages[$i1]["selected"] == 1)
			{
				if ($pages[$i1]["hasChild"] && !$pages[$i1]["isKnot"])
				#if ($pages[$i1]["hasChild"])
					$menuContent .= "<a class='menu_highlight trigger'>".$pages[$i1]["title"]."</a>";
				else
				{
					$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight trigger"'; // T3 hack
					$menuContent .= $this->pi_LinkToPage($pages[$i1]["title"],$pages[$i1]["uid"],"","");
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
				}
				// set banner picture
				$GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"] = $pages[$i1]["tx_nkwsubmenu_picture"];

			}
			else if ($pages[$i1]["selected"] == 2 || $pages[$i1]["selected"] == 3)
			{
				#if ($pages[$i1]["hasChild"])
				if ($pages[$i1]["hasChild"]  && !$pages[$i1]["isKnot"])
					$menuContent .= "<a class='menu_highlight_top trigger'>".$pages[$i1]["title"]."</a>";
				else
				{
					$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight_top trigger"'; // T3 hack
					$menuContent .= $this->pi_LinkToPage($pages[$i1]["title"],$pages[$i1]["uid"],"","");
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack				
				}
			}
			else
			{
				if ($pages[$i1]["hasChild"])
					$menuContent .= "<a class='trigger'>".$pages[$i1]["title"]."</a>";
				else
				{
					$GLOBALS['TSFE']->ATagParams = 'class="trigger"'; // T3 hack
					$menuContent .= $this->pi_LinkToPage($pages[$i1]["title"],$pages[$i1]["uid"],"","");
					$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack
				}
			}

			if ($pages[$i1]["tx_nkwsubmenu_usecontent"])
			{

				// open ul if selected
				if ($pages[$i1]["selected"])
					$menuContent .= "\n\t<ul class='menu_sub js go'>\n";
				else
					$menuContent .= "\n\t<ul class='menu_sub js'>\n";

				foreach ($pages[$i1]["content"] AS $key => $value)
				{
					$menuContent .= "\t\t<li>";
					$menuContent .= $this->pi_LinkToPage($value["header"],$pages[$i1]["uid"]."#c".$value["uid"],"","");
					$menuContent .= "\t\t</li>";
				}

				$menuContent .= "\n\t</ul>";
			}
			
			// SECOND
			if ($pages[$i1]["hasChild"] && (!$pages[$i1]["isKnot"] || $pages[$i1]["selected"]))
			{
				
				// open ul if selected
				if ($pages[$i1]["selected"])
					$menuContent .= "\n\t<ul class='menu_sub js go'>\n";
				else
					$menuContent .= "\n\t<ul class='menu_sub js'>\n";
				
				// cycle children
				for ($i2=0;$i2<sizeof($pages[$i1]["child"]);$i2++)
				{

					$menuContent .= "\t\t<li>";

					// format link
					if ($pages[$i1]["child"][$i2]["selected"] == 1)
					{
						$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight"'; // T3 hack
						$menuContent .= $this->pi_LinkToPage($pages[$i1]["child"][$i2]["title"],$pages[$i1]["child"][$i2]["uid"],"","");
						$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack

						// set banner picture
						$GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"] = $pages[$i1]["child"][$i2]["tx_nkwsubmenu_picture"];

					}
					else if ($pages[$i1]["child"][$i2]["selected"] == 2)
					{
						$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight_top"'; // T3 hack
						$menuContent .= $this->pi_LinkToPage($pages[$i1]["child"][$i2]["title"],$pages[$i1]["child"][$i2]["uid"],"","");
						$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack					
					}
					else
						$menuContent .= $this->pi_LinkToPage($pages[$i1]["child"][$i2]["title"],$pages[$i1]["child"][$i2]["uid"],"","");

					// THIRD
					if ($pages[$i1]["child"][$i2]["hasChild"] && (!$pages[$i1]["child"][$i2]["isKnot"] || $pages[$i1]["child"][$i2]["selected"]))
					{

						$menuContent .= "\n\t\t\t<ul class='menu_sub_2'>\n";

						// cycle children
						for ($i3=0;$i3<sizeof($pages[$i1]["child"][$i2]["child"]);$i3++)
						{

							$menuContent .= "\t\t\t\t<li>";

							// format link
							if ($pages[$i1]["child"][$i2]["child"][$i3]["selected"] == 1)
							{
								$GLOBALS['TSFE']->ATagParams = 'class="menu_highlight"'; // T3 hack
								$menuContent .= $this->pi_LinkToPage($pages[$i1]["child"][$i2]["child"][$i3]["title"],$pages[$i1]["child"][$i2]["child"][$i3]["uid"],"","");
								$GLOBALS['TSFE']->ATagParams = $saveATagParams; // T3 hack

								// set banner picture
								$GLOBALS["TSFE"]->page["tx_nkwsubmenu_picture"] = $pages[$i1]["child"][$i2]["child"][$i3]["tx_nkwsubmenu_picture"];								
								
							}
							else
								$menuContent .= $this->pi_LinkToPage($pages[$i1]["child"][$i2]["child"][$i3]["title"],$pages[$i1]["child"][$i2]["child"][$i3]["uid"],"","");

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
		$content = "<ul id='menu1' class='menu expand'>".$menuContent.$content_tmp."</ul>";

		// show a back to startpage link if in knot
		if ($knot)
		{
			$pageInfo = $this->pageInfo(1,$lang);
			$content = "<ul><li>".$this->pi_LinkToPage($pageInfo["title"],1,"","")."</li></ul>".$content;
		}

		// return
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi1/class.tx_nkwsubmenu_pi1.php']);

?>