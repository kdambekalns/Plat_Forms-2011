<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Cache\Backend;

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
 * Testcase for the PDO cache backend
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PdoBackendTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\FLOW3\Utility\Environment
	 */
	protected $environment;

	/**
	 * @var string
	 */
	protected $fixtureFolder;

	/**
	 * @var string
	 */
	protected $fixtureDB;

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @expectedException \F3\FLOW3\Cache\Exception
	 */
	public function setThrowsExceptionIfNoFrontEndHasBeenSet() {
		$backend = new \F3\FLOW3\Cache\Backend\PdoBackend('Testing');
		$backend->injectEnvironment($this->getMock('F3\FLOW3\Utility\Environment'));
		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToSetAndCheckExistenceInCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$this->assertTrue($backend->has($identifier));
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToSetAndGetEntry() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($data, $fetchedData);
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToRemoveEntryFromCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$backend->remove($identifier);
		$this->assertFalse($backend->has($identifier));
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToOverwriteAnEntryInTheCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$otherData = 'some other data';
		$backend->set($identifier, $otherData);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($otherData, $fetchedData);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findIdentifiersByTagFindsSetEntries() {
		$backend = $this->setUpBackend();

		$data = 'Some data';
		$entryIdentifier = 'MyIdentifier';
		$backend->set($entryIdentifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tag2'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag1');
		$this->assertEquals($entryIdentifier, $retrieved[0]);

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag2');
		$this->assertEquals($entryIdentifier, $retrieved[0]);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setRemovesTagsFromPreviousSet() {
		$backend = $this->setUpBackend();

		$data = 'Some data';
		$entryIdentifier = 'MyIdentifier';
		$backend->set($entryIdentifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tag2'));
		$backend->set($entryIdentifier, $data, array('UnitTestTag%tag3'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag2');
		$this->assertEquals(array(), $retrieved);
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function hasReturnsFalseIfTheEntryDoesntExist() {
		$backend = $this->setUpBackend();
		$identifier = 'NonExistingIdentifier';
		$this->assertFalse($backend->has($identifier));
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function removeReturnsFalseIfTheEntryDoesntExist() {
		$backend = $this->setUpBackend();
		$identifier = 'NonExistingIdentifier';
		$this->assertFalse($backend->remove($identifier));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushByTagRemovesCacheEntriesWithSpecifiedTag() {
		$backend = $this->setUpBackend();

		$data = 'some data' . microtime();
		$backend->set('PdoBackendTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
		$backend->set('PdoBackendTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'));
		$backend->set('PdoBackendTest3', $data, array('UnitTestTag%test'));

		$backend->flushByTag('UnitTestTag%special');

		$this->assertTrue($backend->has('PdoBackendTest1'), 'PdoBackendTest1');
		$this->assertFalse($backend->has('PdoBackendTest2'), 'PdoBackendTest2');
		$this->assertTrue($backend->has('PdoBackendTest3'), 'PdoBackendTest3');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushRemovesAllCacheEntries() {
		$backend = $this->setUpBackend();

		$data = 'some data' . microtime();
		$backend->set('PdoBackendTest1', $data);
		$backend->set('PdoBackendTest2', $data);
		$backend->set('PdoBackendTest3', $data);

		$backend->flush();

		$this->assertFalse($backend->has('PdoBackendTest1'), 'PdoBackendTest1');
		$this->assertFalse($backend->has('PdoBackendTest2'), 'PdoBackendTest2');
		$this->assertFalse($backend->has('PdoBackendTest3'), 'PdoBackendTest3');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushRemovesOnlyOwnEntries() {
		$thisCache = $this->getMock('F3\FLOW3\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$thisCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('thisCache'));
		$thisBackend = $this->setUpBackend();
		$thisBackend->setCache($thisCache);

		$thatCache = $this->getMock('F3\FLOW3\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$thatCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('thatCache'));
		$thatBackend = $this->setUpBackend();
		$thatBackend->setCache($thatCache);

		$thisBackend->set('thisEntry', 'Hello');
		$thatBackend->set('thatEntry', 'World!');
		$thatBackend->flush();

		$this->assertEquals('Hello', $thisBackend->get('thisEntry'));
		$this->assertFalse($thatBackend->has('thatEntry'));
	}

	/**
	 * Sets up the APC backend used for testing
	 *
	 * @return \F3\FLOW3\Cache\Backend\PdoBackend
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function setUpBackend() {
		$mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment');

		$mockSystemLogger = $this->getMock('F3\FLOW3\Log\SystemLoggerInterface');
		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$mockCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('TestCache'));

		$backend = new \F3\FLOW3\Cache\Backend\PdoBackend('Testing');
		$backend->injectEnvironment($mockEnvironment);
		$backend->injectSystemLogger($mockSystemLogger);
		$backend->setCache($mockCache);
		$backend->setDataSourceName('sqlite::memory:');
		$backend->initializeObject();
		$this->backend = $backend;

		return $backend;
	}

}
?>