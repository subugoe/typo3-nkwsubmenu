#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_nkwsubmenu_in_menu tinyint(3) DEFAULT '0' NOT NULL
	tx_nkwsubmenu_exclude_from_menu tinyint(3) DEFAULT '0' NOT NULL
	tx_nkwsubmenu_picture_follow tinyint(3) DEFAULT '0' NOT NULL
	tx_nkwsubmenu_picture_nofollow tinyint(3) DEFAULT '0' NOT NULL
	tx_nkwsubmenu_knot tinyint(3) DEFAULT '0' NOT NULL
	tx_nkwsubmenu_showsidebar tinyint(3) DEFAULT '1' NOT NULL
	tx_nkwsubmenu_hideinfobox tinyint(3) DEFAULT '0' NOT NULL
	tx_nkwsubmenu_knotheader text NOT NULL
);
