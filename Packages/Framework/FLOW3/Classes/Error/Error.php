<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Error;

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
 */

/**
 * An object representation of a generic error. Subclass this to create
 * more specific errors if necessary.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class Error {

	/**
	 * @var string The default (english) error message.
	 */
	protected $message = 'Unknown error';

	/**
	 * @var string The error code
	 */
	protected $code;

	/**
	 * Constructs this error
	 *
	 * @param string $message An english error message which is used if no other error message can be resolved
	 * @param integer $code A unique error code
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function __construct($message, $code) {
		$this->message = $message;
		$this->code = $code;
	}

	/**
	 * Returns the error message
	 * @return string The error message
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @api
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Returns the error code
	 * @return string The error code
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @api
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Converts this error into a string
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function __toString() {
		return $this->message . ' (#' . $this->code . ')';
	}
}

?>