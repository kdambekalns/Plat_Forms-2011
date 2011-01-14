<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Package;

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
 * A Package
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class Package implements PackageInterface {

	const PATTERN_MATCH_PACKAGEKEY = '/^[A-Z][A-Za-z0-9_]+$/';

	const DIRECTORY_CLASSES = 'Classes/';
	const DIRECTORY_CONFIGURATION = 'Configuration/';
	const DIRECTORY_DOCUMENTATION = 'Documentation/';
	const DIRECTORY_METADATA = 'Meta/';
	const DIRECTORY_TESTS_FUNCTIONAL = 'Tests/Functional/';
	const DIRECTORY_RESOURCES = 'Resources/';

	const FILENAME_PACKAGEINFO = 'Package.xml';

	/**
	 * Unique key of this package
	 * @var string
	 */
	protected $packageKey;

	/**
	 * Full path to this package's main directory
	 * @var string
	 */
	protected $packagePath;

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Package\MetaData\ReaderInterface
	 */
	protected $metaDataReader;

	/**
	 * @var \F3\FLOW3\Package\MetaData Meta information about this package
	 */
	protected $packageMetaData;

	/**
	 * @var array Names and relative paths (to this package directory) of files containing classes
	 */
	protected $classFiles;

	/**
	 * Constructor
	 *
	 * @param string $packageKey Key of this package
	 * @param string $packagePath Absolute path to the package's main directory
	 * @throws \F3\FLOW3\Package\Exception\InvalidPackageKeyException if an invalid package key was passed
	 * @throws \F3\FLOW3\Package\Exception\InvalidPackagePathException if an invalid package path was passed
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($packageKey, $packagePath) {
		if (preg_match(self::PATTERN_MATCH_PACKAGEKEY, $packageKey) !== 1) throw new \F3\FLOW3\Package\Exception\InvalidPackageKeyException('"' . $packageKey . '" is not a valid package key.', 1217959510);
		if (!(is_dir($packagePath) || (\F3\FLOW3\Utility\Files::is_link($packagePath) && is_dir(readlink(rtrim($packagePath, '/')))))) throw new \F3\FLOW3\Package\Exception\InvalidPackagePathException('Package path does not exist or is no directory.', 1166631889);
		if (substr($packagePath, -1, 1) != '/') throw new \F3\FLOW3\Package\Exception\InvalidPackagePathException('Package path has no trailing forward slash.', 1166633720);

		$this->packageKey = $packageKey;
		$this->packagePath = $packagePath;
	}

	/**
	 * Injects the Object Manager
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injects a MetaData file reader
	 *
	 * @param \F3\FLOW3\Package\MetaData\ReaderInterface $metaDataReader
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectMetaDataReader(\F3\FLOW3\Package\MetaData\ReaderInterface $metaDataReader) {
		$this->metaDataReader = $metaDataReader;
	}

	/**
	 * Returns the package meta data object of this package.
	 *
	 * @return \F3\FLOW3\Package\MetaData
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPackageMetaData() {
		if ($this->packageMetaData === NULL) {
			$this->packageMetaData = $this->metaDataReader->readPackageMetaData($this);
		}
		return $this->packageMetaData;
	}

	/**
	 * Returns the array of filenames of the class files
	 *
	 * @return array An array of class names (key) and their filename, including the relative path to the package's directory
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getClassFiles() {
		if (!is_array($this->classFiles)) {
			$this->classFiles = $this->buildArrayOfClassFiles($this->packagePath . self::DIRECTORY_CLASSES);
		}
		return $this->classFiles;
	}

	/**
	 * Returns the array of filenames of class files provided by functional tests contained in this package
	 *
	 * @return array An array of class names (key) and their filename, including the relative path to the package's directory
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getFunctionalTestsClassFiles() {
		return $this->buildArrayOfClassFiles($this->packagePath . self::DIRECTORY_TESTS_FUNCTIONAL, 'Tests\\Functional\\');
	}

	/**
	 * Returns the package key of this package.
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getPackageKey() {
		return $this->packageKey;
	}

	/**
	 * Returns the full path to this package's main directory
	 *
	 * @return string Path to this package's main directory
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getPackagePath() {
		return $this->packagePath;
	}

	/**
	 * Returns the full path to this package's Classes directory
	 *
	 * @return string Path to this package's Classes directory
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getClassesPath() {
		return $this->packagePath . self::DIRECTORY_CLASSES;
	}

	/**
	 * Returns the full path to this package's functional tests directory
	 *
	 * @return string Path to this package's functional tests directory
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getFunctionalTestsPath() {
		return $this->packagePath . self::DIRECTORY_TESTS_FUNCTIONAL;
	}

	/**
	 * Returns the full path to this package's Resources directory
	 *
	 * @return string Path to this package's Resources directory
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getResourcesPath() {
		return $this->packagePath . self::DIRECTORY_RESOURCES;
	}

	/**
	 * Returns the full path to this package's Configuration directory
	 *
	 * @return string Path to this package's Configuration directory
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getConfigurationPath() {
		return $this->packagePath . self::DIRECTORY_CONFIGURATION;
	}

	/**
	 * Returns the full path to the package's meta data directory
	 *
	 * @return string Full path to the package's meta data directory
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function getMetaPath() {
		return $this->packagePath . self::DIRECTORY_METADATA;
	}

	/**
	 * Returns the full path to the package's documentation directory
	 *
	 * @return string Full path to the package's documentation directory
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function getDocumentationPath() {
		return $this->packagePath . self::DIRECTORY_DOCUMENTATION;
	}

	/**
	 * Returns the available documentations for this package
	 *
	 * @return array Array of \F3\FLOW3\Package\Documentation
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function getPackageDocumentations() {
		$documentations = array();
		$documentationPath = $this->getDocumentationPath();
		if (is_dir($documentationPath)) {
			$documentationsDirectoryIterator = new \DirectoryIterator($documentationPath);
			$documentationsDirectoryIterator->rewind();
			while ($documentationsDirectoryIterator->valid()) {
				$filename = $documentationsDirectoryIterator->getFilename();
				if ($filename[0] != '.' && $documentationsDirectoryIterator->isDir()) {
					$filename = $documentationsDirectoryIterator->getFilename();
					$documentation = $this->objectManager->create('F3\FLOW3\Package\Documentation', $this, $filename, $documentationPath . $filename . '/');
					$documentations[$filename] = $documentation;
				}
				$documentationsDirectoryIterator->next();
			}
		}
		return $documentations;
	}

	/**
	 * Builds and returns an array of class names => file names of all
	 * *.php files in the package's Classes directory and its sub-
	 * directories.
	 *
	 * @param string $classesPath Base path acting as the parent directory for potential class files
	 * @param string $extraNamespaceSegment A PHP class namespace segment which should be inserted like so: \F3\PackageKey\{namespacePrefix\}PathSegment\PathSegment\Filename
	 * @param string $subDirectory Used internally
	 * @param integer $recursionLevel Used internally
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 * @throws \F3\FLOW3\Package\Exception if recursion into directories was too deep or another error occurred
	 */
	protected function buildArrayOfClassFiles($classesPath, $extraNamespaceSegment = '', $subDirectory = '', $recursionLevel = 0) {
		$classFiles = array();
		$currentPath = $classesPath . $subDirectory;

		if (!is_dir($currentPath)) return array();
		if ($recursionLevel > 100) throw new \F3\FLOW3\Package\Exception('Recursion too deep.', 1166635495);

		try {
			$classesDirectoryIterator = new \DirectoryIterator($currentPath);
			while ($classesDirectoryIterator->valid()) {
				$filename = $classesDirectoryIterator->getFilename();
				if ($filename[0] != '.') {
					if (is_dir($currentPath . $filename)) {
						$classFiles = array_merge($classFiles, $this->buildArrayOfClassFiles($classesPath, $extraNamespaceSegment, $subDirectory . $filename . '/', ($recursionLevel+1)));
					} else {
						if (substr($filename, -4, 4) === '.php') {
							$className = (str_replace('/', '\\', ('F3/' . $this->packageKey . '/' . $extraNamespaceSegment . substr($currentPath, strlen($classesPath)) . substr($filename, 0, -4))));
							$classFiles[$className] = $subDirectory . $filename;
						}
					}
				}
				$classesDirectoryIterator->next();
			}

		} catch(\Exception $exception) {
			throw new \F3\FLOW3\Package\Exception($exception->getMessage(), 1166633720);
		}
		return $classFiles;
	}
}

?>