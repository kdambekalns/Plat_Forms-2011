<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Utility;

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
 * This class can be used as a replacement for superglobals such as $_SERVER etc.
 * to give the caller a hint to use a different way for accessing the information.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 * @scope prototype
 */
class SuperGlobalReplacement extends \ArrayObject {

	/**
	 * @var string Name of the super global which was replaced by this object
	 */
	protected $replacedSuperGlobalName;

	/**
	 * @var string A little hint how to access this super global alternatively
	 */
	protected $accessHintMessage;

	/**
	 * Constructs the super global replacement.
	 *
	 * @param string $replacedSuperGlobalName Name of the super global which was replaced by this object
	 * @param string $accessHintMessage A little hint how to access this super global alternatively
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($replacedSuperGlobalName, $accessHintMessage) {
		$this->replacedSuperGlobalName = $replacedSuperGlobalName;
		$this->accessHintMessage = $accessHintMessage;
	}

	/**
	 * Intercepts count() calls.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function count() {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to run isset() for offsets
	 *
	 * @param mixed $offset The offset to check
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function offsetExists($offset) {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to read array items
	 *
	 * @param string $offset
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function offsetGet($offset) {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to read properties.
	 *
	 * @param string $propertyName Name of the property to get
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __get($propertyName) {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to write properties.
	 *
	 * @param string $propertyName Name of the property to set
	 * @param mixed $value Value to set
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __set($propertyName, $value) {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to call methods
	 *
	 * @param string $methodName Name of the method to call
	 * @param array $arguments An array of arguments
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __call($methodName, $arguments) {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to run isset() on properties
	 *
	 * @param string $propertyName Name of the property to check
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __isset($propertyName) {
		$this->throwException();
	}

	/**
	 * Intercepts all attempts to unset properties.
	 *
	 * @param string $propertyName Name of the property to unset
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __unset($propertyName) {
		$this->throwException();
	}

	/**
	 * Throws an exception on unauthorized access
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function throwException() {
		$debugBacktrace = debug_backtrace();
		if (isset($debugBackTrace[2])) {
			$callingMethod = $debugBacktrace[2]['class'] . $debugBacktrace[2]['type'] . $debugBacktrace[2]['function'] . ' (' . $debugBacktrace[1]['file'] . ' line ' . $debugBacktrace[1]['line'] . ')';
		} else {
			$callingMethod = 'in file ' . $debugBacktrace[1]['file'];
		}
		$message = sprintf('You tried to access the $%s super global in %s but access to this variable has been restricted. %s', $this->replacedSuperGlobalName, $callingMethod, $this->accessHintMessage);
		throw new \F3\FLOW3\Utility\Exception($message, 1176548856);
	}
}
?>