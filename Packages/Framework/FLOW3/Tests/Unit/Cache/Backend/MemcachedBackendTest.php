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
 * Testcase for the cache to memcached backend
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class MemcachedBackendTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\FLOW3\Utility\Environment
	 */
	protected $environment;

	/**
	 * Sets up this testcase
	 *
	 * @return void
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function setUp() {
		if (!extension_loaded('memcache')) {
			$this->markTestSkipped('memcache extension was not available');
		}

		try {
			if (!fsockopen('localhost', 11211)) {
				$this->markTestSkipped('memcached not reachable');
			}
		} catch (\Exception $e) {
			$this->markTestSkipped('memcached not reachable');
		}

		$this->environment = new \F3\FLOW3\Utility\Environment();
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @expectedException \F3\FLOW3\Cache\Exception
	 */
	public function setThrowsExceptionIfNoFrontEndHasBeenSet() {
		$backendOptions = array('servers' => array('localhost:11211'));
		$backend = new \F3\FLOW3\Cache\Backend\MemcachedBackend('Testing', $backendOptions);
		$backend->injectEnvironment($this->environment);
		$backend->initializeObject();
		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 * @expectedException \F3\FLOW3\Cache\Exception
	 */
	public function initializeObjectThrowsExceptionIfNoMemcacheServerIsConfigured() {
		$backend = new \F3\FLOW3\Cache\Backend\MemcachedBackend('Testing');
		$backend->initializeObject();
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @expectedException \F3\FLOW3\Cache\Exception
	 */
	public function setThrowsExceptionIfConfiguredServersAreUnreachable() {
		$backend = $this->setUpBackend(array('servers' => array('julle.did.this:1234')));
		$data = 'Somedata';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data);
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToSetAndCheckExistenceInCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data);
		$inCache = $backend->has($identifier);
		$this->assertTrue($inCache, 'Memcache failed to set and check entry');
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToSetAndGetEntry() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($data, $fetchedData, 'Memcache failed to set and retrieve data');
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToRemoveEntryFromCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data);
		$backend->remove($identifier);
		$inCache = $backend->has($identifier);
		$this->assertFalse($inCache, 'Failed to set and remove data from Memcache');
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function itIsPossibleToOverwriteAnEntryInTheCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data);
		$otherData = 'some other data';
		$backend->set($identifier, $otherData);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($otherData, $fetchedData, 'Memcache failed to overwrite and retrieve data');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findIdentifiersByTagFindsCacheEntriesWithSpecifiedTag() {
		$backend = $this->setUpBackend();

		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tag2'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag1');
		$this->assertEquals($identifier, $retrieved[0], 'Could not retrieve expected entry by tag.');

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag2');
		$this->assertEquals($identifier, $retrieved[0], 'Could not retrieve expected entry by tag.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setRemovesTagsFromPreviousSet() {
		$backend = $this->setUpBackend();

		$data = 'Some data';
		$identifier = uniqid('MyIdentifier');
		$backend->set($identifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tagX'));
		$backend->set($identifier, $data, array('UnitTestTag%tag3'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tagX');
		$this->assertEquals(array(), $retrieved, 'Found entry which should no longer exist.');
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function hasReturnsFalseIfTheEntryDoesntExist() {
		$backend = $this->setUpBackend();
		$identifier = uniqid('NonExistingIdentifier');
		$inCache = $backend->has($identifier);
		$this->assertFalse($inCache,'"has" did not return false when checking on non existing identifier');
	}

	/**
	 * @test
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	public function removeReturnsFalseIfTheEntryDoesntExist() {
		$backend = $this->setUpBackend();
		$identifier = uniqid('NonExistingIdentifier');
		$inCache = $backend->remove($identifier);
		$this->assertFalse($inCache,'"remove" did not return false when checking on non existing identifier');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushByTagRemovesCacheEntriesWithSpecifiedTag() {
		$backend = $this->setUpBackend();

		$data = 'some data' . microtime();
		$backend->set('BackendMemcacheTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
		$backend->set('BackendMemcacheTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'));
		$backend->set('BackendMemcacheTest3', $data, array('UnitTestTag%test'));

		$backend->flushByTag('UnitTestTag%special');

		$this->assertTrue($backend->has('BackendMemcacheTest1'), 'BackendMemcacheTest1');
		$this->assertFalse($backend->has('BackendMemcacheTest2'), 'BackendMemcacheTest2');
		$this->assertTrue($backend->has('BackendMemcacheTest3'), 'BackendMemcacheTest3');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushRemovesAllCacheEntries() {
		$backend = $this->setUpBackend();

		$data = 'some data' . microtime();
		$backend->set('BackendMemcacheTest1', $data);
		$backend->set('BackendMemcacheTest2', $data);
		$backend->set('BackendMemcacheTest3', $data);

		$backend->flush();

		$this->assertFalse($backend->has('BackendMemcacheTest1'), 'BackendMemcacheTest1');
		$this->assertFalse($backend->has('BackendMemcacheTest2'), 'BackendMemcacheTest2');
		$this->assertFalse($backend->has('BackendMemcacheTest3'), 'BackendMemcacheTest3');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushRemovesOnlyOwnEntries() {
		$mockSystemLogger = $this->getMock('F3\FLOW3\Log\SystemLoggerInterface');
		$backendOptions = array('servers' => array('localhost:11211'));

		$thisCache = $this->getMock('F3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$thisCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('thisCache'));
		$thisBackend = new \F3\FLOW3\Cache\Backend\MemcachedBackend('Testing', $backendOptions);
		$thisBackend->injectEnvironment($this->environment);
		$thisBackend->injectSystemLogger($mockSystemLogger);
		$thisBackend->setCache($thisCache);
		$thisBackend->initializeObject();

		$thatCache = $this->getMock('F3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$thatCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('thatCache'));
		$thatBackend = new \F3\FLOW3\Cache\Backend\MemcachedBackend('Testing', $backendOptions);
		$thatBackend->injectEnvironment($this->environment);
		$thatBackend->injectSystemLogger($mockSystemLogger);
		$thatBackend->setCache($thatCache);
		$thatBackend->initializeObject();

		$thisBackend->set('thisEntry', 'Hello');
		$thatBackend->set('thatEntry', 'World!');
		$thatBackend->flush();

		$this->assertEquals('Hello', $thisBackend->get('thisEntry'));
		$this->assertFalse($thatBackend->has('thatEntry'));
	}

	/**
	 * Check if we can store ~5 MB of data, this gives some headroom for the
	 * reflection data.
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function largeDataIsStored() {
		$backend = $this->setUpBackend();

		$data = str_repeat('abcde', 1024 * 1024);
		$backend->set('tooLargeData', $data);

		$this->assertTrue($backend->has('tooLargeData'));
		$this->assertEquals($backend->get('tooLargeData'), $data);
	}

	/**
	 * Sets up the memcached backend used for testing
	 *
	 * @param array $backendOptions Options for the memcache backend
	 * @return \F3\FLOW3\Cache\Backend\MemcachedBackend
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function setUpBackend(array $backendOptions = array()) {
		$mockSystemLogger = $this->getMock('F3\FLOW3\Log\SystemLoggerInterface');
		$cache = $this->getMock('F3\FLOW3\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		if ($backendOptions == array()) {
			$backendOptions = array('servers' => array('localhost:11211'));
		}
		$backend = new \F3\FLOW3\Cache\Backend\MemcachedBackend('Testing', $backendOptions);
		$backend->injectEnvironment($this->environment);
		$backend->injectSystemLogger($mockSystemLogger);
		$backend->setCache($cache);
		$backend->initializeObject();
		return $backend;
	}

}
?>