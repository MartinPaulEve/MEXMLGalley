<?php

/**
 * @file meXml.inc.php
 *
 * Copyright (c) 2011 Martin Paul Eve
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Portions copyright (c) 2003-2011 John Willinsky
 *
 * @class meXml
 * @ingroup plugins_generic_meXml
 *
 * @brief Martin Eve's modified XML Galley Plugin
 */


import('classes.article.ArticleGalley');
import('classes.article.ArticleFileDAO');
import('lib.pkp.classes.plugins.GenericPlugin');
//import('plugins.generic.xmlGalley.ArticleXMLGalley');

class meXml extends GenericPlugin {
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				$this->import('ArticleXMLGalleyDAO');
				$xmlGalleyDao = new ArticleXMLGalleyDAO($this->getName());
				DAORegistry::registerDAO('ArticleXMLGalleyDAO', $xmlGalleyDao);

				HookRegistry::register('ArticleGalleyDAO::insertNewGalley', array(&$xmlGalleyDao, 'insertXMLGalleys') );
				HookRegistry::register('ArticleGalleyDAO::_returnGalleyFromRow', array(&$this, 'returnXMLGalley') );
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



	/**
	 * Append some special attributes to a galley identified as XML, and
	 * Return an ArticleXMLGalley object as appropriate
	 */
	function returnXMLGalley($hookName, $args) {
		
		if (!$this->getEnabled()) return false;
		$galley =& $args[0];
		$row =& $args[1];

		// If the galley is an XML file, then convert it from an HTML Galley to an XML Galley
		if ($galley->getFileType() == "text/xml") {
			//$galley = $this->_returnXMLGalleyFromArticleGalley($galley);
			error_log("Return called");
			return true;
		}

		return false;
	}



	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
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
		if (!parent::manage($verb, $args, $message)) { return false; } else { return true; }
	}
}
?>
