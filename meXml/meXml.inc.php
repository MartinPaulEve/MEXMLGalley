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
				HookRegistry::register('ArticleGalleyDAO::insertNewGalley', array(&$this, 'insertXMLGalleys') );
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



	// TODO: move this to own class file for new xmlGalleyFile etc.

	/**
	 * Insert XML-derived galleys into article_xml_galleys
	 */
	function insertXMLGalleys($hookName, $args) {

		$galley =& $args[0];
		$galleyId =& $args[1];

		// If the galley is an XML file, then insert rows in the article_xml_galleys table
		if ($galley->getLabel() == "XML") {

			// create an XHTML galley
			$this->update(
				'INSERT INTO article_xml_galleys
					(galley_id, article_id, label, galley_type)
					VALUES
					(?, ?, ?, ?)',
				array(
					$galleyId,
					$galley->getArticleId(),
					'XHTML',
					'application/xhtml+xml'
				)
			);

			// if we have enabled XML-PDF galley generation (plugin setting)
			// and are using the built-in NLM stylesheet, append a PDF galley as well
			// this will insert a second corresponding entry into article_galleys first in order
			// to circumvent bug #5152 by only ever having one galley per file


			// instantiate a new galley file
			$ArticleGalley = new ArticleGalley();

			$ArticleGalley->setArticleId($galley->getArticleId());
			$ArticleGalley->setLabel('PDF');

			// insert the new galley
			$ArticleGalleyDao = new ArticleGalleyDAO();
			$ArticleGalleyDao->insertArticleFile($ArticleGalley);

			// insert the PDF/XML galley
			$journal =& Request::getJournal();
			$xmlGalleyPlugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);

			if ($xmlGalleyPlugin->getSetting($journal->getId(), 'nlmPDF') == 1 && 
				$xmlGalleyPlugin->getSetting($journal->getId(), 'XSLstylesheet') == 'NLM' ) {

				// create a PDF galley
				$this->update(
					'INSERT INTO article_xml_galleys
						(galley_id, article_id, label, galley_type)
						VALUES
						(?, ?, ?, ?)',
					array(
						$ArticleGalley->getId(),
						$galley->getArticleId(),
						'PDF',
						'application/pdf'
					)
				);

			}
			return true;
		}
		return false;	}


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
