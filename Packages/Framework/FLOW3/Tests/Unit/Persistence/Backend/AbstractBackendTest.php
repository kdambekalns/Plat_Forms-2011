<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Persistence\Backend;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3CR".                    *
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

require_once(__DIR__ . '/../Fixture/AnEntity.php');
require_once(__DIR__ . '/../Fixture/AValue.php');

/**
 * Testcase for \F3\FLOW3\Persistence\Backend
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AbstractBackendTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeAsksReflectionServiceForClassSchemata() {
		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchemata');
		$backend = $this->getMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$backend->injectReflectionService($mockReflectionService);
		$backend->initialize(array());
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function commitDelegatesToPersistObjectsAndProcessDeletedObjects() {
		$backend = $this->getMock('F3\FLOW3\Persistence\Backend\AbstractBackend', array('persistObjects', 'processDeletedObjects', 'getObjectCountByQuery', 'getObjectDataByQuery', 'getObjectDataByIdentifier', 'removeEntity', 'removeValueObject', 'storeObject'));
		$backend->expects($this->once())->method('persistObjects');
		$backend->expects($this->once())->method('processDeletedObjects');
		$backend->commit();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectsPassesObjectsToPersistObject() {
		$objects = new \SplObjectStorage();
		$objects->attach(new \stdClass());
		$objects->attach(new \stdClass());

		$mockPersistenceSession = $this->getMock('F3\FLOW3\Persistence\Session');
		$mockPersistenceSession->expects($this->once())->method('getReconstitutedEntities')->will($this->returnValue(clone $objects));
		$backend = $this->getAccessibleMock('F3\FLOW3\Persistence\Backend\AbstractBackend', array('persistObject', 'getObjectCountByQuery', 'getObjectDataByQuery', 'getObjectDataByIdentifier', 'removeEntity', 'removeValueObject', 'storeObject'));

		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->expects($this->exactly(4))->method('persistObject');
		$backend->setAggregateRootObjects($objects);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processDeletedObjectsPassesObjectsToRemoveEntity() {
		$object = new \stdClass();
		$objects = new \SplObjectStorage();
		$objects->attach($object);

		$mockSession = $this->getMock('F3\FLOW3\Persistence\Session');
		$mockSession->expects($this->at(0))->method('hasObject')->with($object)->will($this->returnValue(TRUE));
		$mockSession->expects($this->at(1))->method('unregisterReconstitutedEntity')->with($object);
		$mockSession->expects($this->at(2))->method('unregisterObject')->with($object);

		$backend = $this->getAccessibleMock('F3\FLOW3\Persistence\Backend\AbstractBackend', array('getObjectCountByQuery', 'getObjectDataByQuery', 'getObjectDataByIdentifier', 'removeEntity', 'removeValueObject', 'storeObject'));
		$backend->injectPersistenceSession($mockSession);
		$backend->expects($this->once())->method('removeEntity')->with($object);
		$backend->setDeletedEntities($objects);
		$backend->_call('processDeletedObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processDeletedObjectsPassesOnlyKnownObjectsToRemoveEntity() {
		$object = new \stdClass();
		$objects = new \SplObjectStorage();
		$objects->attach($object);

		$mockSession = $this->getMock('F3\FLOW3\Persistence\Session');
		$mockSession->expects($this->at(0))->method('hasObject')->with($object)->will($this->returnValue(FALSE));
		$mockSession->expects($this->never())->method('unregisterObject');

		$backend = $this->getAccessibleMock('F3\FLOW3\Persistence\Backend\AbstractBackend', array('getObjectCountByQuery', 'getObjectDataByQuery', 'getObjectDataByIdentifier', 'removeEntity', 'removeValueObject', 'storeObject'));
		$backend->injectPersistenceSession($mockSession);
		$backend->expects($this->never())->method('removeEntity');
		$backend->setDeletedEntities($objects);
		$backend->_call('processDeletedObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getTypeNormalizesDoubleToFloat() {
		$backend = $this->getAccessibleMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$this->assertEquals('float', $backend->_call('getType', 1.234));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getTypeReturnsClassNameForObjects() {
		$backend = $this->getAccessibleMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$this->assertEquals('stdClass', $backend->_call('getType', new \stdClass()));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function arrayContainsObjectReturnsTrueForSameObject() {
		$object = new \stdClass();

		$backend = $this->getAccessibleMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$this->assertTrue($backend->_call('arrayContainsObject', array($object), $object));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function arrayContainsObjectReturnsFalseForDifferentObject() {
		$backend = $this->getAccessibleMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$this->assertFalse($backend->_call('arrayContainsObject', array(new \stdClass()), new \stdClass()));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function arrayContainsObjectReturnsFalseForClone() {
		$object = new \stdClass();

		$backend = $this->getAccessibleMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$this->assertFalse($backend->_call('arrayContainsObject', array($object), clone $object));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function arrayContainsObjectReturnsTrueForSameEntity() {
		$object = new \stdClass();
		$object->FLOW3_Persistence_Entity_UUID = 'fakeUuid';
		$object->property = 'foo';

		$clone = clone $object;
		$clone->property = 'bar';

		$backend = $this->getAccessibleMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$this->assertTrue($backend->_call('arrayContainsObject', array($object), $clone));
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function initializeIgnoresAndLogsNullValuesForInvalidOptions() {
		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchemata');

		$mockSystemLogger = $this->getMock('F3\FLOW3\Log\SystemLoggerInterface');
		$mockSystemLogger->expects($this->atLeastOnce())->method('log');

		$backend = $this->getMockForAbstractClass('F3\FLOW3\Persistence\Backend\AbstractBackend');
		$backend->injectReflectionService($mockReflectionService);
		$backend->injectSystemLogger($mockSystemLogger);
		$backend->initialize(array('optionNotValid' => NULL));
	}

}

?>