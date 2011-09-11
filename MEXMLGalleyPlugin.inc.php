<?php

/**
 * @file MEXMLGalleyPlugin.inc.php
 *
 * Copyright (c) 2011 Martin Paul Eve
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MEXMLGalleyPlugin
 * @ingroup plugins_generic_MEXMLGalley
 *
 * @brief Martin Eve's modified XML Galley Plugin
 */

// $Id$


import('lib.pkp.classes.plugins.GenericPlugin');

// this plugin is symbiotic with xmlGalley; it can't function independently
//include_once('plugins/generic/xmlGalley/ArticleXMLGalleyDAO.inc.php');

class MEXMLGalleyPlugin extends GenericPlugin {
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

	function getDisplayName() {
		return "Martin Eve's modified XML Galley Plugin";
	}

	function getDescription() {
		return "Allows PDF galleys to be generated from XML";
	}

	function insertXMLGalleys($hookName, $args) {

		error_log("Function called");
/*
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
			);*/

// WARNING: The below code is disabled because of bug #5152. When a galley
// exists with the same galley_id as an entry in the article_xml_galleys table,
// editing the XML galley will corrupt the entry in the galleys table for the
// same galley_id. This has been fixed by retiring the article_xml_galleys
// table's xml_galley_id in favour of using the galley_id instead, but this
// means that only a single derived galley (=XHTML) is possible for an XML
// galley upload.
/*

			// if we have enabled XML-PDF galley generation (plugin setting)
			// and are using the built-in NLM stylesheet, append a PDF galley as well
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
						$galleyId,
						$galley->getArticleId(),
						'PDF',
						'application/pdf'
					)
				);

			}*//*
			return true;
		}
		return false;*/

		return false;
	}



	/**
	 * Return XML-derived galley by ID from article_xml_galleys
	 * (which does not exist in article_galleys)
	 */
	function getXMLGalley($hookName, $args) {
		if (!$this->getEnabled()) return false;
		return false;
		$galleyId =& $args[0];
		$articleId =& $args[1];
		$returner =& $args[2];

		$xmlGalleyDao = new ArticleXMLGalleyDAO($this->getName());
		$xmlGalley = $xmlGalleyDao->_getXMLGalleyFromId($galleyId, $articleId);
		if ($xmlGalley) {
			$xmlGalley->setId($galleyId);
			$returner = $xmlGalley;
			return true;
		}
		return false;
	}

	/**
	 * Return XML-derived galley as a file; basically this is a FO-rendered PDF file
	 */
	function viewXMLGalleyFile($hookName, $args) {
		if (!$this->getEnabled()) return false;
		$article =& $args[0];
		$galley =& $args[1];
		$fileId =& $args[2];

		$journal =& Request::getJournal();

		if (get_class($galley) == 'ArticleXMLGalley' && $galley->isPdfGalley() &&
			$this->getSetting($journal->getId(), 'nlmPDF') == 1) {
			return $galley->viewFileContents();
		} else return false;
	}



	/**
	 * Internal function to return an ArticleXMLGalley object from an ArticleGalley object
	 * @param $galley ArticleGalley
	 * @return ArticleXMLGalley
	 */
	function _returnXMLGalleyFromArticleGalley(&$galley) {
		$this->import('ArticleXMLGalley');
		$articleXMLGalley = new ArticleXMLGalley($this->getName());

		// Create XML Galley with previous values
		$articleXMLGalley->setId($galley->getId());
		$articleXMLGalley->setArticleId($galley->getArticleId());
		$articleXMLGalley->setFileId($galley->getFileId());
		$articleXMLGalley->setLabel($galley->getLabel());
		$articleXMLGalley->setSequence($galley->getSequence());
		$articleXMLGalley->setViews($galley->getViews());
		$articleXMLGalley->setFileName($galley->getFileName());
		$articleXMLGalley->setOriginalFileName($galley->getOriginalFileName());
		$articleXMLGalley->setFileType($galley->getFileType());
		$articleXMLGalley->setFileSize($galley->getFileSize());
		$articleXMLGalley->setDateModified($galley->getDateModified());
		$articleXMLGalley->setDateUploaded($galley->getDateUploaded());
		$articleXMLGalley->setLocale($galley->getLocale());

		$articleXMLGalley->setType('public');

		// Copy CSS and image file references from source galley
		if ($galley->isHTMLGalley()) {
			$articleXMLGalley->setStyleFileId($galley->getStyleFileId());
			$articleXMLGalley->setStyleFile($galley->getStyleFile());
			$articleXMLGalley->setImageFiles($galley->getImageFiles());
		}

		return $articleXMLGalley;
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
