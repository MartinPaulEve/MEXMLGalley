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

				HookRegistry::register('ArticleGalleyDAO::getArticleGalleys', array(&$xmlGalleyDao, 'appendXMLGalleys') );
				HookRegistry::register('ArticleGalleyDAO::insertNewGalley', array(&$xmlGalleyDao, 'insertXMLGalleys') );
				HookRegistry::register('ArticleGalleyDAO::deleteGalleyById', array(&$xmlGalleyDao, 'deleteXMLGalleys') );
				HookRegistry::register('ArticleGalleyDAO::incrementGalleyViews', array(&$xmlGalleyDao, 'incrementXMLViews') );
				HookRegistry::register('ArticleGalleyDAO::_returnGalleyFromRow', array(&$this, 'returnXMLGalley') );
				HookRegistry::register('ArticleGalleyDAO::getNewGalley', array(&$this, 'getXMLGalley') );

				// This hook is required in the absence of hooks in the viewFile and download methods
				HookRegistry::register( 'ArticleHandler::viewFile', array(&$this, 'viewXMLGalleyFile') );
				HookRegistry::register( 'ArticleHandler::downloadFile', array(&$this, 'viewXMLGalleyFile') );

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
	 * Get the filename of the ADODB schema for this plugin.
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/' . 'schema.xml';
	}

	/**
	 * Return XML-derived galley by ID from article_xml_galleys
	 * (which does not exist in article_galleys)
	 */
	function getXMLGalley($hookName, $args) {
		if (!$this->getEnabled()) return false;
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
	 * Append some special attributes to a galley identified as XML, and
	 * Return an ArticleXMLGalley object as appropriate
	 */
	function returnXMLGalley($hookName, $args) {
		
		if (!$this->getEnabled()) return false;
		$galley =& $args[0];
		$row =& $args[1];

		// If the galley is an XML file, then convert it from an HTML Galley to an XML Galley
		if ($galley->getFileType() == "text/xml") {
			$galley = $this->_returnXMLGalleyFromArticleGalley($galley);
			return true;
		}

		return false;
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
		parent::setEnabled($enabled);
		$journal =& Request::getJournal();
		if ($journal) {
			// set default XSLT renderer
			if ($this->getSetting($journal->getId(), 'XSLTrenderer') == "") {

				// Determine the appropriate XSLT processor for the system
				if ( version_compare(PHP_VERSION,'5','>=') && extension_loaded('xsl') && extension_loaded('dom') ) {
					// PHP5.x with XSL/DOM modules
					$this->updateSetting($journal->getId(), 'XSLTrenderer', 'PHP5');

				} elseif ( version_compare(PHP_VERSION,'5','<') && extension_loaded('xslt') ) {
					// PHP4.x with XSLT module
					$this->updateSetting($journal->getId(), 'XSLTrenderer', 'PHP4');

				} else {
					$this->updateSetting($journal->getId(), 'XSLTrenderer', 'external');
				}
			}

			// set default XSL stylesheet to NLM
			if ($this->getSetting($journal->getId(), 'XSLstylesheet') == "") {
				$this->updateSetting($journal->getId(), 'XSLstylesheet', 'NLM');
			}

			return true;
		}
		return false;
	}

	/**
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', Locale::translate('plugins.generic.xmlGalley.manager.settings'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/*
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message) {
		if (!parent::manage($verb, $args, $message)) return false;

		$journal =& Request::getJournal();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));

		$this->import('XMLGalleySettingsForm');
		$form = new XMLGalleySettingsForm($this, $journal->getId());

		switch ($verb) {
			case 'test':
				// test external XSLT renderer
				$xsltRenderer = $this->getSetting($journal->getId(), 'XSLTrenderer');

				if ($xsltRenderer == "external") {
					// get command for external XSLT tool
					$xsltCommand = $this->getSetting($journal->getId(), 'externalXSLT');

					// get test XML/XSL files
					$xmlFile = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . $this->getPluginPath() . '/transform/test.xml';
					$xslFile = $this->getPluginPath() . '/transform/test.xsl';

					// create a testing article galley object (to access the XSLT render method)
					$this->import('ArticleXMLGalley');
					$xmlGalley = new ArticleXMLGalley($this->getName());

					// transform the XML using whatever XSLT processor we have available
					$result = $xmlGalley->transformXSLT($xmlFile, $xslFile, $xsltCommand);

					// check the result
					if (trim(preg_replace("/\s+/", " ", $result)) != "Open Journal Systems Success" ) {
						$form->addError('content', Locale::translate('plugins.generic.xmlGalley.settings.externalXSLTFailure'));
					} else $templateMgr->assign('testSuccess', true);

				}

			case 'settings':
				Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON,  LOCALE_COMPONENT_PKP_MANAGER));
				// if we are updating XSLT settings or switching XSL sheets
				if (Request::getUserVar('save')) {
					$form->readInputData();
					$form->initData();
					if ($form->validate()) {
						$form->execute();
					}
					$form->display();

				// if we are uploading a custom XSL sheet
				} elseif (Request::getUserVar('uploadCustomXSL')) {
					$form->readInputData();

					import('classes.file.JournalFileManager');

					// if the a valid custom XSL is uploaded, process it
					$fileManager = new JournalFileManager($journal);
					if ($fileManager->uploadedFileExists('customXSL')) {

						// check type and extension -- should be text/xml and xsl, respectively
						$type = $fileManager->getUploadedFileType('customXSL');
						$fileName = $fileManager->getUploadedFileName('customXSL');
						$extension = strtolower($fileManager->getExtension($fileName));

						if (($type == 'text/xml' || $type == 'text/xml' || $type == 'application/xml' || $type == 'application/xslt+xml')
							&& $extension == 'xsl') {

							// if there is an existing XSL file, delete it from the journal files folder
							$existingFile = $this->getSetting($journal->getId(), 'customXSL');
							if (!empty($existingFile) && $fileManager->fileExists($fileManager->filesDir . $existingFile)) {
								$fileManager->deleteFile($existingFile);
							}

							// upload the file into the journal files folder
							$fileManager->uploadFile('customXSL', $fileName);

							// update the plugin and form settings
							$this->updateSetting($journal->getId(), 'XSLstylesheet', 'custom');
							$this->updateSetting($journal->getId(), 'customXSL', $fileName);

						} else $form->addError('content', Locale::translate('plugins.generic.xmlGalley.settings.customXSLInvalid'));

					} else $form->addError('content', Locale::translate('plugins.generic.xmlGalley.settings.customXSLRequired'));

					// re-populate the form values with the new settings
					$form->initData();
					$form->display();

				// if we are deleting an existing custom XSL sheet
				} elseif (Request::getUserVar('deleteCustomXSL')) {

					import('classes.file.JournalFileManager');

					// if the a valid custom XSL is uploaded, process it
					$fileManager = new JournalFileManager($journal);

					// delete the file from the journal files folder
					$fileName = $this->getSetting($journal->getId(), 'customXSL');
					if (!empty($fileName)) $fileManager->deleteFile($fileName);

					// update the plugin and form settings
					$this->updateSetting($journal->getId(), 'XSLstylesheet', 'NLM');
					$this->updateSetting($journal->getId(), 'customXSL', '');


					$form->initData();
					$form->display();

				} else {
					$form->initData();
					$form->display();
				}
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
}
?>
