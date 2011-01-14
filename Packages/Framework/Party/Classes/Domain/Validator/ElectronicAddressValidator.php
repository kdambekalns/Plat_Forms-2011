<?php
declare(ENCODING = 'utf-8');
namespace F3\Party\Domain\Validator;

/*                                                                        *
 * This script belongs to the FLOW3 package "Party".                      *
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
 * An electronic address validator
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class ElectronicAddressValidator extends \F3\FLOW3\Validation\Validator\AbstractValidator {

	/**
	 * @var \F3\FLOW3\Validation\ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * Injects the validator resolver
	 *
	 * @param \F3\FLOW3\Validation\ValidatorResolver $validatorResolver
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectValidatorResolver(\F3\FLOW3\Validation\ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * Checks if the given value is a valid electronic address according to its type.
	 *
	 * If at least one error occurred, the result is FALSE and any errors can
	 * be retrieved through the getErrors() method.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function isValid($value) {
		$this->errors = array();
		if ($value instanceof \F3\Party\Domain\Model\ElectronicAddress) {
			$addressValidator = $this->validatorResolver->createValidator($value->getType() . 'Address');
			if ($addressValidator === NULL) {
				$this->addError('No validator found for electronic address of type "' . $value->getType() . '".', 1268676030);
				return FALSE;
			}
			$result = $addressValidator->isValid($value->getIdentifier());
			$this->errors = array_merge($this->errors, $addressValidator->getErrors());
			return $result;
		} elseif ($value === NULL) {
			return TRUE;
		}
	}

}
?>