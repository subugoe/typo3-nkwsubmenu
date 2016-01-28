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
 */
class tx_nkwsubmenu_pi2 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin
{

    public $prefixId = 'tx_nkwsubmenu_pi2';
    public $scriptRelPath = 'pi2/class.tx_nkwsubmenu_pi2.php';
    public $extKey = 'nkwsubmenu';
    public $pi_checkCHash = true;

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $db;

    /**
     * The main method of the PlugIn
     *
     * @param string $content The PlugIn content
     * @param array $conf The PlugIn configuration
     * @return string content that is displayed on the website
     */
    public function main($content, $conf)
    {
        $this->conf = $conf;
        $this->db = $GLOBALS['TYPO3_DB'];
        $this->pi_setPiVarDefaults();

        $view = $this->initializeTemplate();

        $id = $this->checkForAlienContent($GLOBALS['TSFE']->id);
        if (!$id) {
            $id = $GLOBALS['TSFE']->id;
        }

        // get page content
        $view->assign('pageContent', $this->getPageContent($id));
        $view->assign('extendedContent', $this->getExtendedContent());
        $view->assign('contentPictures', $this->getContentPictures());
        $view->assign('ownSidebar', $this->getOwnSidebar());

        return $view->render();
    }

    /**
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    protected function initializeTemplate()
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setTemplateRootPaths(['EXT:nkwsubmenu/Resources/Private/Templates/']);
        $view->setTemplate('Main');

        return $view;
    }

    /**
     *  does anybody want to have a completely own sidebar?
     * @return string
     */
    protected function getOwnSidebar()
    {
        $content = '';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['ownSidebar'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['ownSidebar'] as $userFunc) {
                if ($userFunc) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($userFunc, $content, $this);
                }
            }
        }

        return $content;
    }

    /**
     * Insert pictures in side-menu via hook
     *
     * @return string
     */
    protected function getContentPictures()
    {
        $contentPictures = '';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages'] as $userFunc) {
                if ($userFunc) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($userFunc, $contentPictures, $this);
                }
            }
        }

        return $contentPictures;
    }

    /**
     * Get additional string content via hook
     *
     * @return string
     */
    protected function getExtendedContent()
    {
        $extendedContent = '';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC'] as $userFunc) {
                if ($userFunc) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($userFunc, $extendedContent, $this);
                }
            }
        }

        return $extendedContent;
    }

    /**
     * check if a page uses the content of another page "content_from_pid"
     *
     * @param int $id
     * @return mixed
     */
    protected function checkForAlienContent($id)
    {
        $res1 = $this->db->exec_SELECTquery(
            'uid, content_from_pid',
            'pages',
            'uid = ' . $id . $this->cObj->enableFields('pages'),
            '',
            '',
            ''
        );

        while ($row1 = $this->db->sql_fetch_assoc($res1)) {
            $contentFromPid = $row1['content_from_pid'];
        }
        if (isset($contentFromPid)) {
            $return = $contentFromPid;
        } else {
            $return = false;
        }
        return $return;
    }

    /**
     * Returns an Array Containing the UID and header field of content elements
     * of a page
     * If no content element it returns false
     *
     * @param int $id
     * @return mixed
     */
    protected function getPageContent($id)
    {

        /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
        $pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);

        $content = $pageRepository->getRecordsByField(
            'tt_content',
            'pid',
            $GLOBALS['TSFE']->id,
            'AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid . $GLOBALS['TSFE']->sys_page->enableFields('tt_content'),
            '',
            'sorting ASC'
        );

        return $content;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwsubmenu/pi2/class.tx_nkwsubmenu_pi2.php']);
}
