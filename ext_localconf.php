<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi2/class.tx_nkwsubmenu_pi2.php', '_pi2',
    'list_type', 1);
