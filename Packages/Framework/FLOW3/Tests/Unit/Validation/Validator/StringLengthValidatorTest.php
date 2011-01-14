<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Validation\Validator;

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
 * Testcase for the string length validator
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StringLengthValidatorTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function internalErrorsArrayIsResetOnIsValidCall() {
		$validator = $this->getAccessibleMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('dummy'), array(), '', FALSE);
		$validator->_set('errors', array('existingError'));
		$validator->isValid('foo');
		$this->assertSame(array(), $validator->getErrors());
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueForAStringShorterThanMaxLengthAndLongerThanMinLength() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 0, 'maximum' => 50));
		$this->assertTrue($stringLengthValidator->isValid('this is a very simple string'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsFalseForAStringShorterThanThanMinLength() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 50, 'maximum' => 100));
		$this->assertFalse($stringLengthValidator->isValid('this is a very short string'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsFalseForAStringLongerThanThanMaxLength() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 5, 'maximum' => 10));
		$this->assertFalse($stringLengthValidator->isValid('this is a very short string'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueForAStringLongerThanThanMinLengthAndMaxLengthNotSpecified() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 5));
		$this->assertTrue($stringLengthValidator->isValid('this is a very short string'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueForAStringShorterThanThanMaxLengthAndMinLengthNotSpecified() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('maximum' => 100));
		$this->assertTrue($stringLengthValidator->isValid('this is a very short string'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueForAStringLengthEqualToMaxLengthAndMinLengthNotSpecified() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('maximum' => 10));
		$this->assertTrue($stringLengthValidator->isValid('1234567890'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueForAStringLengthEqualToMinLengthAndMaxLengthNotSpecified() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 10));
		$this->assertTrue($stringLengthValidator->isValid('1234567890'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueIfMinLengthAndMaxLengthAreEqualAndTheGivenStringMatchesThisValue() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 10, 'maximum' => 10));
		$this->assertTrue($stringLengthValidator->isValid('1234567890'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueIfTheStringLengthIsEqualToMaxLength() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 1, 'maximum' => 10));
		$this->assertTrue($stringLengthValidator->isValid('1234567890'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorReturnsTrueIfTheStringLengthIsEqualToMinLength() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 10, 'maximum' => 100));
		$this->assertTrue($stringLengthValidator->isValid('1234567890'));
	}

	/**
	 * @test
	 * @expectedException F3\FLOW3\Validation\Exception\InvalidValidationOptionsException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorThrowsAnExceptionIfMinLengthIsGreaterThanMaxLength() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 101, 'maximum' => 100));
		$stringLengthValidator->isValid('1234567890');
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorInsertsAnErrorObjectIfValidationFails() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->expects($this->once())->method('addError');
		$stringLengthValidator->setOptions(array('minimum' => 50, 'maximum' => 100));

		$stringLengthValidator->isValid('this is a very short string');
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorCanHandleAnObjectWithAToStringMethod() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 5, 'maximum' => 100));

		$className = uniqid('TestClass');

		eval('
			class ' . $className . ' {
				public function __toString() {
					return \'some string\';
				}
			}
		');

		$object = new $className();
		$this->assertTrue($stringLengthValidator->isValid($object));
	}

	/**
	 * @test
	 * @expectedException F3\FLOW3\Validation\Exception\InvalidSubjectException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function stringLengthValidatorThrowsAnExceptionIfTheGivenObjectCanNotBeConvertedToAString() {
		$stringLengthValidator = $this->getMock('F3\FLOW3\Validation\Validator\StringLengthValidator', array('addError'), array(), '', FALSE);
		$stringLengthValidator->setOptions(array('minimum' => 5, 'maximum' => 100));

		$className = uniqid('TestClass');

		eval('
			class ' . $className . ' {
				protected $someProperty;
			}
		');

		$object = new $className();
		$stringLengthValidator->isValid($object);
	}
}

?>