<?php

/**
 * @file meXml.inc.php
 *
 * Copyright (c) 2011 Martin Paul Eve
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class meXml
 * @ingroup plugins_generic_meXml
 *
 * @brief Martin Eve's modified XML Galley Plugin
 */

// $Id$


import('lib.pkp.classes.plugins.GenericPlugin');

// this plugin is symbiotic with xmlGalley; it can't function independently
//include_once('plugins/generic/xmlGalley/ArticleXMLGalleyDAO.inc.php');

class meXml extends GenericPlugin {
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				error_log("Registered");
				HookRegistry::register('ArticleGalleyDAO::insertNewGalley', array($this, 'insertXMLGalleys') );
			}

			return true;
		}
		return false;
	}

	function getName() {
		return 'meXml';
	}

	function getDisplayName() {
		return "Martin Eve's modified XML Galley Plugin";
	}

	function getDescription() {
		return "Allows PDF galleys to be generated from XML";
	}

	function insertXMLGalleys($hookName, $args) {

		error_log("Function called");

		return false;
	}



	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
		error_log("Set to " . $enabled);
		parent::setEnabled($enabled);
		$journal =& Request::getJournal();
		if ($journal) {
			return true;
		}
		return false;
	}

	/*
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message) {
		error_log("Verb " . $verb);
		if (!parent::manage($verb, $args, $message)) { return false; } else { return true; }
	}
}
?>
