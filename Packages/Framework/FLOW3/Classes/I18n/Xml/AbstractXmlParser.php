<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n\Xml;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * An abstract class for all concrete classes that parses any kind of XML data.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractXmlParser {

	/**
	 * Associative array of "filename => parsed data" pairs.
	 *
	 * @var array
	 */
	protected $parsedFiles;

	/**
	 * Returns parsed representation of XML file.
	 *
	 * Parses XML if it wasn't done before. Caches parsed data.
	 *
	 * @param string $sourcePath An absolute path to XML file
	 * @return array Parsed XML file
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function getParsedData($sourcePath) {
		if (!isset($this->parsedFiles[$sourcePath])) {
			$this->parsedFiles[$sourcePath] = $this->parseXmlFile($sourcePath);
		}
		return $this->parsedFiles[$sourcePath];
	}

	/**
	 * Reads and parses XML file and returns internal representation of data.
	 *
	 * @param string $sourcePath An absolute path to XML file
	 * @return array Parsed XML file
	 * @throws \F3\FLOW3\I18n\Xml\Exception\InvalidXmlFileException When SimpleXML couldn't load XML file
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function parseXmlFile($sourcePath) {
		if (file_exists($sourcePath)) {
			$rootXmlNode = simplexml_load_file($sourcePath, 'SimpleXmlElement', \LIBXML_NOWARNING);
		}

		if (!isset($rootXmlNode) || $rootXmlNode === FALSE) {
			throw new \F3\FLOW3\I18n\Xml\Exception\InvalidXmlFileException('The path provided does not point to existing and accessible well-formed XML file.', 1278155987);
		}

		return $this->doParsingFromRoot($rootXmlNode);
	}

	/**
	 * Returns array representation of XML data, starting from a root node.
	 *
	 * @param \SimpleXMLElement $root A root node
	 * @return array An array representing parsed XML file (structure depends on concrete parser)
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	abstract protected function doParsingFromRoot(\SimpleXMLElement $root);
}

?>