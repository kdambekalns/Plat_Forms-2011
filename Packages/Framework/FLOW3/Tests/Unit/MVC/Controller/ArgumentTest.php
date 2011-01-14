<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\MVC\Controller;

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
 * Testcase for the MVC Controller Argument
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ArgumentTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setUp() {
		$this->mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 * @expectedException \InvalidArgumentException
	 */
	public function constructingArgumentWithoutNameThrowsException() {
		new \F3\FLOW3\MVC\Controller\Argument(NULL, 'Text');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function constructingArgumentWithInvalidNameThrowsException() {
		new \F3\FLOW3\MVC\Controller\Argument(new \ArrayObject(), 'Text');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function passingDataTypeToConstructorReallySetsTheDataType() {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('dummy', 'Number');
		$this->assertEquals('Number', $argument->getDataType(), 'The specified data type has not been set correctly.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setShortNameProvidesFluentInterface() {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('dummy', 'Text');
		$returnedArgument = $argument->setShortName('x');
		$this->assertSame($argument, $returnedArgument, 'The returned argument is not the original argument.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setValueProvidesFluentInterface() {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('dummy', 'Text');
		$returnedArgument = $argument->setValue('x');
		$this->assertSame($argument, $returnedArgument, 'The returned argument is not the original argument.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setValueUsesNullAsIs() {
		$argument = $this->getMock('F3\FLOW3\MVC\Controller\Argument', array('transformValue'), array('dummy', 'ArrayObject'));
		$argument->expects($this->never())->method('transformValue');
		$argument->setValue(NULL);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setValueUsesMatchingInstanceAsIs() {
		$argument = $this->getMock('F3\FLOW3\MVC\Controller\Argument', array('transformValue'), array('dummy', '\ArrayObject'));
		$argument->expects($this->never())->method('transformValue');
		$argument->setValue(new \ArrayObject());
	}

	/**
	 * @test
	 * @dataProvider argumentRawValuesAndPhpValues
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setValueConvertsSimpleTypesIntoTheirCorrespondingPhpType($rawValue, $dataType, $phpValue) {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('argumentName', $dataType);
		$argument->setValue($rawValue);
		$this->assertSame($phpValue, $argument->getValue());
	}

	/**
	 * Data provider for - see above.
	 *
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function argumentRawValuesAndPhpValues() {
		return array(
			 array('dummy', 'Text', 'dummy'),
			 array('dummy', 'string', 'dummy'),
			 array('', 'string', ''),

			 array('1', 'integer', 1),
			 array('-1', 'integer', -1),
			 array('0', 'integer', 0),
			 array('', 'integer', NULL),

			 array('0', 'float', (float)0),
			 array('1.0', 'float', 1.0),
			 array('1.1', 'float', 1.1),
			 array('-2.1', 'float', -2.1),
			 array('', 'float', NULL),

			 array('0', 'double', (float)0),
			 array('1.0', 'double', 1.0),
			 array('1.1', 'double', 1.1),
			 array('-2.1', 'double', -2.1),
			 array('', 'double', NULL),

			 array('1', 'boolean', TRUE),
			 array('0', 'boolean', FALSE),
			 array('-1', 'boolean', FALSE),
			 array('true', 'boolean', TRUE),
			 array('false', 'boolean', FALSE),
			 array('TRUE', 'boolean', TRUE),
			 array('FALSE', 'boolean', FALSE),
			 array('', 'boolean', NULL),
		 );
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setValueTriesToConvertAnUuidStringIntoTheRealObjectIfDataTypeClassSchemaIsAvailable() {
		$object = new \stdClass();

		$mockClassSchema = $this->getMock('F3\FLOW3\Reflection\ClassSchema', array(), array() ,'', FALSE);
		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getObjectByIdentifier')->with('e104e469-9030-4b98-babf-3990f07dd3f1')->will($this->returnValue($object));

		$argument = $this->getAccessibleMock('F3\FLOW3\MVC\Controller\Argument', array('findObjectByIdentityUUID'), array(), '', FALSE);
		$argument->injectPersistenceManager($mockPersistenceManager);
		$argument->_set('dataTypeClassSchema', $mockClassSchema);
		$argument->_set('dataType', 'stdClass');
		$argument->setValue('e104e469-9030-4b98-babf-3990f07dd3f1');

		$this->assertSame($object, $argument->_get('value'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setValueHandsArraysOverToThePropertyMapperIfDataTypeClassSchemaIsAvailable() {
		$object = new \stdClass();

		$mockClassSchema = $this->getMock('F3\FLOW3\Reflection\ClassSchema', array(), array() ,'', FALSE);
		$mockPropertyMapper = $this->getMock('F3\FLOW3\Property\PropertyMapper');
		$mockPropertyMapper->expects($this->once())->method('map')->with(array('foo'), array('foo' => 'bar'), 'stdClass')->will($this->returnValue($object));

		$argument = $this->getAccessibleMock('F3\FLOW3\MVC\Controller\Argument', array('dummy'), array(), '', FALSE);
		$argument->injectPropertyMapper($mockPropertyMapper);
		$argument->_set('dataTypeClassSchema', $mockClassSchema);
		$argument->_set('dataType', 'stdClass');
		$argument->setValue(array('foo' => 'bar'));

		$this->assertSame($object, $argument->_get('value'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @expectedException \F3\FLOW3\MVC\Exception\InvalidArgumentValueException
	 */
	public function setValueThrowsExceptionIfValueIsNotInstanceOfDataType() {
		$mockClassSchema = $this->getMock('F3\FLOW3\Reflection\ClassSchema', array(), array() ,'', FALSE);
		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getObjectByIdentifier')->will($this->returnValue(new \stdClass()));

		$mockMappingResults = $this->getMock('F3\FLOW3\Property\MappingResults', array('hasErrors'), array(), '', FALSE);
		$mockMappingResults->expects($this->any())->method('hasErrors')->will($this->returnValue(FALSE));
		$mockPropertyMapper = $this->getMock('F3\FLOW3\Property\PropertyMapper', array('getMappingResults'), array(), '', FALSE);
		$mockPropertyMapper->expects($this->any())->method('getMappingResults')->will($this->returnValue($mockMappingResults));

		$argument = $this->getAccessibleMock('F3\FLOW3\MVC\Controller\Argument', array('findObjectByIdentityUUID'), array(), '', FALSE);
		$argument->injectPersistenceManager($mockPersistenceManager);
		$argument->injectPropertyMapper($mockPropertyMapper);
		$argument->_set('dataTypeClassSchema', $mockClassSchema);
		$argument->_set('dataType', 'ArrayObject');
		$argument->setValue('e104e469-9030-4b98-babf-3990f07dd3f1');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setValueTriesToMapObjectIfDataTypeClassSchemaIsNotSet() {
		$object = new \stdClass();
		$object->title = 'Hello';

		$mockPropertyMapper = $this->getMock('F3\FLOW3\Property\PropertyMapper');
		$mockPropertyMapper->expects($this->once())->method('map')->with(array('title'), array('title' => 'Hello'), 'stdClass')->will($this->returnValue($object));

		$argument = $this->getAccessibleMock('F3\FLOW3\MVC\Controller\Argument', array('findObjectByIdentityUUID'), array(), '', FALSE);
		$argument->_set('dataType', 'stdClass');
		$argument->injectPropertyMapper($mockPropertyMapper);


		$argument->setValue(array('title' => 'Hello'));
		$this->assertSame($object, $argument->_get('value'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \F3\FLOW3\MVC\Exception\InvalidArgumentValueException
	 */
	public function setValueThrowsExceptionIfComplexObjectShouldBeGeneratedFromStringAndDataTypeClassSchemaIsNotSet() {
		$argument = $this->getAccessibleMock('F3\FLOW3\MVC\Controller\Argument', array('findObjectByIdentityUUID'), array(), '', FALSE);
		$argument->_set('dataType', 'stdClass');

		$argument->setValue(42);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setShortHelpMessageProvidesFluentInterface() {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('dummy', 'Text');
		$returnedArgument = $argument->setShortHelpMessage('x');
		$this->assertSame($argument, $returnedArgument, 'The returned argument is not the original argument.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function toStringReturnsTheStringVersionOfTheArgumentsValue() {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('dummy', 'Text');
		$argument->setValue(123);

		$this->assertSame((string)$argument, '123', 'The returned argument is not a string.');
		$this->assertNotSame((string)$argument, 123, 'The returned argument is identical to the set value.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setDefaultValueReallySetsDefaultValue() {
		$argument = new \F3\FLOW3\MVC\Controller\Argument('dummy', 'Text');
		$argument->injectObjectManager($this->mockObjectManager);
		$argument->setDefaultValue(42);

		$this->assertEquals(42, $argument->getValue(), 'The default value was not stored in the Argument.');
	}

}
?>