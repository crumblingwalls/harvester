<?php

/**
 * @file pages/admin/AdminFunctionsHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminFunctionsHandler
 *
 * Handle requests for site administrative/maintenance functions. 
 *
 */

import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionDAO');
import('lib.pkp.classes.site.VersionCheck');
import('pages.admin.AdminHandler');

class AdminFunctionsHandler extends AdminHandler {

	/**
	 * Show system information summary.
	 */
	function systemInfo($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$configData =& Config::getData();

		$dbconn =& DBConnection::getConn();
		$dbServerInfo = $dbconn->ServerInfo();

		$versionDao = DAORegistry::getDAO('VersionDAO');
		$currentVersion =& $versionDao->getCurrentVersion();
		$versionHistory =& $versionDao->getVersionHistory();

		$serverInfo = array(
			'admin.server.platform' => Core::serverPHPOS(),
			'admin.server.phpVersion' => Core::serverPHPVersion(),
			'admin.server.apacheVersion' => (function_exists('apache_get_version') ? apache_get_version() : __('common.notAvailable')),
			'admin.server.dbDriver' => Config::getVar('database', 'driver'),
			'admin.server.dbVersion' => (empty($dbServerInfo['description']) ? $dbServerInfo['version'] : $dbServerInfo['description'])
		);

		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign_by_ref('currentVersion', $currentVersion);
		$templateMgr->assign_by_ref('versionHistory', $versionHistory);
		$templateMgr->assign_by_ref('configData', $configData);
		$templateMgr->assign_by_ref('serverInfo', $serverInfo);
		if ($request->getUserVar('versionCheck')) {
			$latestVersionInfo =& VersionCheck::getLatestVersion();
			$latestVersionInfo['patch'] = VersionCheck::getPatch($latestVersionInfo);
			$templateMgr->assign_by_ref('latestVersionInfo', $latestVersionInfo);
		}
		$templateMgr->display('admin/systemInfo.tpl');
	}

	/**
	 * Show full PHP configuration information.
	 */
	function phpinfo($args, &$request) {
		$this->validate();
		phpinfo();
	}

	/**
	 * Expire all user sessions (will log out all users currently logged in).
	 */
	function expireSessions($args, &$request) {
		$this->validate();
		$sessionDao = DAORegistry::getDAO('SessionDAO');
		$sessionDao->deleteAllSessions();
		$request->redirect('admin');
	}

	/**
	 * Clear compiled templates.
	 */
	function clearTemplateCache($args, &$request) {
		$this->validate();
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->clearTemplateCache();
		$request->redirect('admin');
	}

	/**
	 * Clear the data cache.
	 */
	function clearDataCache($args, &$request) {
		$this->validate();
		import('lib.pkp.classes.cache.CacheManager');
		$cacheManager =& CacheManager::getManager($request);
		$cacheManager->flush();
		$request->redirect('admin');
	}
}

?>
