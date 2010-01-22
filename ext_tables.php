<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
t3lib_extMgm::addPlugin(array('LLL:EXT:nkwsubmenu/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","SUB Menu");

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';
t3lib_extMgm::addPlugin(array('LLL:EXT:nkwsubmenu/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","SUB Infobox");

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:nkwsubmenu/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi3/static/","Keywordlist");

$tempColumns = Array (
	"tx_nkwsubmenu_in_menu" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu",
		"config" => Array (
			"type" => "select",
			"items" => Array(
				Array("LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.0", "0"),
                Array("LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.1", "1"),
                Array("LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.2", "2"),
                Array("LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.3", "3"),
			),
		)
	),
	"tx_nkwsubmenu_picture" => Array (        
		"exclude" => 1,        
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_picture",
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],
			"max_size" => 500,
			"uploadfolder" => "uploads/tx_nkwsubmenu",
			"show_thumbs" => 1,
			"size" => 1,
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_nkwsubmenu_picture_follow" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_picture_follow",
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_nkwsubmenu_picture_nofollow" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_picture_nofollow",
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_nkwsubmenu_knot" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_knot",
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_nkwsubmenu_usecontent" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_usecontent",
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_nkwsubmenu_knotheader" => Array (        
		"exclude" => 1,        
		"label" => "LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_knotheader",
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],
			"max_size" => 500,
			"uploadfolder" => "uploads/tx_nkwsubmenu",
			"show_thumbs" => 1,
			"size" => 1,
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);

t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_nkwsubmenu_in_menu;;;;1-1-1,tx_nkwsubmenu_picture,tx_nkwsubmenu_picture_follow,tx_nkwsubmenu_picture_nofollow,tx_nkwsubmenu_knot,tx_nkwsubmenu_usecontent,tx_nkwsubmenu_knotheader");

?>