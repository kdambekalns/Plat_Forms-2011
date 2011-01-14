<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Validation\Validator;

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
 * Validator for integers
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class IntegerValidator extends \F3\FLOW3\Validation\Validator\AbstractValidator {

	/**
	 * Checks if the given value is a valid integer.
	 *
	 * If at least one error occurred, the result is FALSE.
	 *
	 * @param mixed $value The value that should be validated
	 * @param \F3\FLOW3\Validation\Errors $errors An Errors object which will contain any errors which occurred during validation
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function isValid($value) {
		$this->errors = array();
		if (filter_var($value, FILTER_VALIDATE_INT) !== FALSE) return TRUE;
		$this->addError('A valid integer number is expected.', 1221560494);
		return FALSE;
	}
}

?>