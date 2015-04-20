<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Nils K. Windisch <windisch@sub.uni-goettingen.de>
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
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi2'] = 'layout,select_key,pages';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
		array('LLL:EXT:nkwsubmenu/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY . '_pi2'),
		'list_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'pi2/static/', 'SUB Infobox');

$tempColumns = array(
		'tx_nkwsubmenu_in_menu' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu',
				'config' => array(
						'type' => 'select',
						'items' => array(
								array('LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.0', '0'),
								array('LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.1', '1'),
								array('LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.2', '2'),
								array('LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_in_menu.I.3', '3'),
						),
				)
		),
		'tx_nkwsubmenu_picture_follow' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_picture_follow',
				'config' => array('type' => 'check')
		),
		'tx_nkwsubmenu_picture_nofollow' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_picture_nofollow',
				'config' => array('type' => 'check')
		),
		'tx_nkwsubmenu_knot' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_knot',
				'config' => array('type' => 'check')
		),
		'tx_nkwsubmenu_showsidebar' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_showsidebar',
				'config' => array('type' => 'check')
		),
		'tx_nkwsubmenu_knotheader' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:nkwsubmenu/locallang_db.xml:pages.tx_nkwsubmenu_knotheader',
				'config' => array(
						'type' => 'group',
						'internal_type' => 'file',
						'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
						'max_size' => 500,
						'uploadfolder' => 'uploads/tx_nkwsubmenu',
						'show_thumbs' => 1,
						'size' => 1,
						'minitems' => 0,
						'maxitems' => 1,
				)
		),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_nkwsubmenu_in_menu;;;;1-1-1,tx_nkwsubmenu_picture_follow,tx_nkwsubmenu_picture_nofollow,tx_nkwsubmenu_knot,tx_nkwsubmenu_usecontent,tx_nkwsubmenu_showsidebar,tx_nkwsubmenu_knotheader');
