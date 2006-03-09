<?php

/**
 * HarvesterPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Abstract class for harvester plugins
 *
 * $Id$
 */

class HarvesterPlugin extends Plugin {
	/** @var $errors array */
	var $errors;

	function HarvesterPlugin() {
		parent::Plugin();
		$this->errors = array();
	}

	/**
	 * Register this plugin for all the appropriate hooks.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			// Permits the plugins to display additional fields
			// on the harvester create/edit form.
			HookRegistry::register('Template::Admin::Archives::displayHarvesterForm', array(&$this, '_smartyDisplayHarvesterForm'));
			HookRegistry::register('ArchiveForm::getParameterNames', array(&$this, '_getArchiveFormParameterNames'));
			HookRegistry::register('ArchiveForm::ArchiveForm', array(&$this, '_extendArchiveFormConstructor'));
			HookRegistry::register('ArchiveForm::initData', array(&$this, '_readAdditionalFormData'));
			HookRegistry::register('ArchiveForm::execute', array(&$this, '_saveAdditionalFormData'));
			HookRegistry::register('ArchiveForm::display', array(&$this, '_displayArchiveForm'));
			HookRegistry::register('Template::Admin::Archives::manage', array(&$this, '_displayManagementInfo'));
		}
		return $success;
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getProtocolDisplayName() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get the symbolic name of this plugin. Should be unique within
	 * the category.
	 */
	function getName() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Handle the Smarty hook to display extra form elements for each
	 * harvester's Create / Edit Archive form. Each harvester plugin
	 * should supply its own harvesterForm.tpl template.
	 */
	function _smartyDisplayHarvesterForm($hookName, $args) {
		$params =& $args[0];
		$smarty =& $args[1];
		$output =& $args[2];

		if (isset($params['plugin']) && $params['plugin'] == $this->getName()) {
			$this->addLocaleData();
			$output .= $smarty->fetch($this->getTemplatePath() . '/harvesterForm.tpl');
			return true;
		}
		return false;
	}

	/**
	 * This is a hook wrapper.
	 */
	function _extendArchiveFormConstructor($hookName, $args) {
		$form =& $args[0];
		$harvesterPlugin = $args[1];

		if ($harvesterPlugin == $this->getName()) {
			$this->addArchiveFormChecks($form);
			return true;
		}
		return false;
	}

	/**
	 * This is a hook wrapper.
	 */
	function _getArchiveFormParameterNames($hookName, $args) {
		$form =& $args[0];
		$parameterNames =& $args[1];
		$harvesterPlugin = $args[2];

		if ($harvesterPlugin == $this->getName()) {
			$additionalFieldNames = $this->getAdditionalArchiveFormFields();
			$parameterNames = array_merge($parameterNames, $additionalFieldNames);
			return true;
		}
		return false;
	}

	/**
	 * This function gives harvester plugins the chance to register
	 * form requirements for the administrator's Archive form.
	 * @param $form object
	 */
	function addArchiveFormChecks(&$form) {
		// Subclasses should add any required validators to the
		// supplied form.
	}

	/**
	 * Get a list of the additional form field names used by this plugin.
	 * Should correspond to all the parameters named in the plugin's
	 * archiveForm.tpl template.
	 */
	function getAdditionalArchiveFormFields() {
		// Subclasses should override this as required.
		return array();
	}

	/**
	 * Initialize the Archive Edit/Create form with data for this plugin.
	 * (This should not tamper with the regular form fields, e.g. title,
	 * description, url, etc.)
	 */
	function initializeArchiveForm(&$form, &$archive) {
		foreach ($this->getAdditionalArchiveFormFields() as $field) {
			$form->setData($field, $archive->getSetting($field));
		}
	}

	/**
	 * Initialize the Archive form's fields. This is a hook wrapper
	 * that calls initializeArchiveForm.
	 */
	function _readAdditionalFormData($hookName, $args) {
		$form =& $args[0];
		$archive =& $args[1];
		$harvesterPlugin =& $args[2];

		if ($harvesterPlugin == $this->getName() && $archive) {
			$this->initializeArchiveForm($form, $archive);
			return true;
		}
		return false;
	}

	/**
	 * This is a hook wrapper that is responsible for saving this
	 * harvester's additional form fields for the administrator's
	 * archive form.
	 */
	function _saveAdditionalFormData($hookName, $args) {
		$form =& $args[0];
		$archive =& $args[1];
		$harvesterPlugin =& $args[2];

		if ($harvesterPlugin == $this->getName() && $archive) {
			foreach ($this->getAdditionalArchiveFormFields() as $field) {
				$archive->updateSetting($field, Request::getUserVar($field));
			}
			$this->executeArchiveForm(&$form, &$archive);
			return true;
		}
		return false;
	}

	/**
	 * This is a hook wrapper that is responsible for calling
	 * displayArchiveForm. Subclasses should override
	 * displayArchiveForm as necessary.
	 */
	function _displayArchiveForm($hookName, $args) {
		$form =& $args[0];
		$templateMgr =& $args[1];
		$harvesterPlugin =& $args[2];
		
		if ($harvesterPlugin == $this->getName()) {
			$this->displayArchiveForm($form, $templateMgr);
			return true;
		}
		return false;
	}

	/**
	 * This is a hook wrapper that is responsible for calling
	 * displayArchiveForm. Subclasses should override
	 * displayArchiveForm as necessary.
	 */
	function _displayManagementInfo($hookName, $args) {
		$params =& $args[0];
		$smarty =& $args[1];
		$output =& $args[2];

		if ($params['plugin'] == $this->getName()) {
			$output = $this->displayManagementInfo($smarty);
			return true;
		}
		return false;
	}

	/**
	 * This function is called when the display() function of the
	 * administrator's archive form is called. Subclasses should
	 * override this function as necessary.
	 * @param $form object
	 * @param $templateMgr object
	 */
	function displayArchiveForm(&$form, &$templateMgr) {
	}

	/**
	 * This function is called when the execute() function of the
	 * administrator's archive form is called. Subclasses should
	 * override this function as necessary.
	 * @param $form object
	 * @param $archive object
	 */
	function executeArchiveForm(&$form, &$archive) {
	}

	/**
	 * This function is called when displaying the management
	 * page for an archive to give the harvester plugin a chance
	 * to display statistics about the archive.
	 */
	function displayManagementInfo(&$smarty) {
	}

	/**
	 * This function is called to update an archive's metadata.
	 * It should be overridden by subclasses.
	 * @param $archive object
	 */
	function updateIndex(&$archive) {
		// Subclasses should override this method
	}

	/**
	 * Add an error message to this harvester plugin.
	 */
	function addError($error) {
		array_push($this->errors, $error);
	}

	/**
	 * Get the error messages associated with this plugin.
	 */
	function getErrors() {
		return $this->errors;
	}
}

?>
