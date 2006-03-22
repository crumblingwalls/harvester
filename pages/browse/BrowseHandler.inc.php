<?php

/**
 * BrowseHandler.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.browse
 *
 * Handle requests for browse functions. 
 *
 * $Id$
 */

class BrowseHandler extends Handler {

	/**
	 * Display site admin index page.
	 */
	function index($args) {
		BrowseHandler::validate();
		$templateMgr = &TemplateManager::getManager();

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$recordDao =& DAORegistry::getDAO('RecordDAO');

		$archiveId = array_shift($args);
		$archive = null;
		if ($archiveId === 'all' || ($archive =& $archiveDao->getArchive($archiveId))) {
			BrowseHandler::setupTemplate($archive, true);

			$rangeInfo = Handler::getRangeInfo('records');
			$sortId = Request::getUserVar('sortId');
			$templateMgr->assign('sortId', $sortId);

			// The user has chosen an archive or opted to browse all
			$records =& $recordDao->getRecords(
				$archive?$archiveId:null, 
				empty($sortId)?array():array($sortId), 
				$rangeInfo
			);

			if ($archive) {
				$fieldDao =& DAORegistry::getDAO('FieldDAO');
				$schemaPlugin =& $archive->getSchemaPlugin();
				$sortableFieldNames = $schemaPlugin->getSortFields();
				$sortableFields = array();
				foreach ($sortableFieldNames as $name) {
					$sortableFields[] =& $fieldDao->buildField($name, $schemaPlugin->getName());
				}
				$templateMgr->assign('sortableFields', $sortableFields);
			} else {
				$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
				$sortableCrosswalks =& $crosswalkDao->getSortableCrosswalks();
				$templateMgr->assign_by_ref('sortableCrosswalks', $sortableCrosswalks);
			}

			$templateMgr->assign_by_ref('records', $records);
			$templateMgr->assign_by_ref('archive', $archive);
			$templateMgr->display('browse/records.tpl');
		} else {
			BrowseHandler::setupTemplate($archive);

			// List archives for the user to browse.
			$rangeInfo = Handler::getRangeInfo('archives');

			$archives =& $archiveDao->getArchives($rangeInfo);

			$templateMgr->assign_by_ref('archives', $archives);
			$templateMgr->display('browse/index.tpl');
		}
	}

	/**
	 * Setup common template variables.
	 * @param $archive object optional
	 * @param $isSubclass boolean optional
	 */
	function setupTemplate(&$archive, $isSubclass = null) {
		$templateMgr = &TemplateManager::getManager();
		$hierarchy = array();
		if ($isSubclass) {
			$hierarchy[] = array(Request::url('browse'), 'navigation.browse');
		}
		if ($archive) {
			$hierarchy[] = array(Request::url('browse', 'index', $archive->getArchiveId()), $archive->getTitle(), true);
		}
		$templateMgr->assign('pageHierarchy', $hierarchy);
	}
}

?>
