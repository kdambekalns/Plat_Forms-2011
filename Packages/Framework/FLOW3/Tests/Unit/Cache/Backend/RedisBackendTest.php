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
 * Testcase for the cache to redis backend
 *
 * This class has functional tests as well as implementation test:
 * - The functional tests make API calls to the backend and check expected behaviour
 * - The implementation tests make additional calls with an own redis instance to
 *   check stored data structures in the redis server, which can not be checked
 *   by functional tests alone. Those tests will fail if any changes
 *   to the internal data structure are done.
 *
 * Warning:
 * The unit tests use and flush redis database numbers 0 and 1!
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RedisBackendTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * If set, the tearDown() method will flush the cache used by this unit test.
	 *
	 * @var \F3\FLOW3\Cache\Backend\RedisBackend
	 */
	protected $backend = NULL;

	/**
	 * Own redis instance used in implementation tests
	 *
	 * @var \Redis
	 */
	protected $redis = NULL;

	/**
	 * Set up this testcase
	 *
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setUp() {
		if (!extension_loaded('redis')) {
			$this->markTestSkipped('redis extension was not available');
		}

		try {
			if (!@fsockopen('127.0.0.1', 6379)) {
				$this->markTestSkipped('redis server not reachable');
			}
		} catch (\Exception $e) {
			$this->markTestSkipped('redis server not reachable');
		}

		$this->backupGlobals = TRUE;
	}

	/**
	 * Sets up the redis backend used for testing
	 *
	 * @param array Options for the memcache backend
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	protected function setUpBackend(array $backendOptions = array()) {
		$mockSystemLogger = $this->getMock('F3\FLOW3\Log\SystemLoggerInterface');

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$mockCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('TestCache'));

		$this->backend = new \F3\FLOW3\Cache\Backend\RedisBackend('Testing', $backendOptions);
		$this->backend->injectSystemLogger($mockSystemLogger);
		$this->backend->setCache($mockCache);
		$this->backend->initializeObject();
	}

	/**
	 * Sets up an own redis instance for implementation tests
	 *
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	protected function setUpRedis() {
		$this->redis = new \Redis();
		$this->redis->connect('127.0.0.1', 6379);
	}

	/**
	 * Tear down this testcase
	 *
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function tearDown() {
		if ($this->backend instanceof \F3\FLOW3\Cache\Backend\RedisBackend) {
			$this->backend->flush();
		}
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function initializeObjectThrowsNoExceptionIfPasswordOptionIsSet() {
		try {
			$this->setUpBackend(array('password' => 'foo'));
		} catch (Exception $e) {
			$this->assertTrue();
		}
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function initializeObjectThrowsNoExceptionIfGivenDatabaseWasSuccessfullySelected() {
		try {
			$this->setUpBackend(array('database' => 1));
		} catch (Exception $e) {
			$this->assertTrue();
		}
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setDatabaseThrowsExceptionIfGivenDatabaseNumberIsNotAnInteger() {
		$this->setUpBackend(array('database' => 'foo'));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setDatabaseThrowsExceptionIfGivenDatabaseNumberIsNegative() {
		$this->setUpBackend(array('database' => -1));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setCompressionThrowsExceptionIfCompressionParameterIsNotOfTypeBoolean() {
		$this->setUpBackend(array('compression' => 'foo'));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setCompressionLevelThrowsExceptionIfCompressionLevelIsNotInteger() {
		$this->setUpBackend(array('compressionLevel' => 'foo'));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setCompressionLevelThrowsExceptionIfCompressionLevelIsNotBetweenMinusOneAndNine() {
		$this->setUpBackend(array('compressionLevel' => 11));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setThrowsExceptionIfIdentifierIsNotAString() {
		$this->setUpBackend();
		$this->backend->set(array(), 'data');
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \F3\FLOW3\Cache\Exception\InvalidDataException
	 */
	public function setThrowsExceptionIfDataIsNotAString() {
		$this->setUpBackend();
		$this->backend->set('identifier' . uniqid(), array());
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setThrowsExceptionIfLifetimeIsNegative() {
		$this->setUpBackend();
		$this->backend->set('identifier' . uniqid(), 'data', array(), -42);
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function setThrowsExceptionIfLifetimeIsNotNullOrAnInteger() {
		$this->setUpBackend();
		$this->backend->set('identifier' . uniqid(), 'data', array(), array());
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setStoresEntriesInSelectedDatabase() {
		$this->setUpRedis();
		$this->redis->select(1);

		$this->setUpBackend(array('database' => 1));
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');

		$this->assertTrue($this->redis->exists('identData:' . $identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesStringDataTypeForIdentifierToDataEntry() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');

		$this->assertSame(\Redis::REDIS_STRING, $this->redis->type('identData:' . $identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesEntryWithDefaultLifeTime() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$defaultLifetime = 42;
		$this->backend->setDefaultLifetime($defaultLifetime);
		$this->backend->set($identifier, 'data');

		$lifetimeRegisteredInBackend = $this->redis->ttl('identData:' . $identifier);
		$this->assertSame($defaultLifetime, $lifetimeRegisteredInBackend);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesEntryWithSpecifiedLifeTime() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$lifetime = 43;
		$this->backend->set($identifier, 'data', array(), $lifetime);

		$lifetimeRegisteredInBackend = $this->redis->ttl('identData:' . $identifier);
		$this->assertSame($lifetime, $lifetimeRegisteredInBackend);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesEntryWithUnlimitedLifeTime() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data', array(), 0);

		$lifetimeRegisteredInBackend = $this->redis->ttl('identData:' . $identifier);
		$this->assertSame(31536000, $lifetimeRegisteredInBackend);
	}

	/**
	 * @test Functional
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setOverwritesExistingEntryWithNewData() {
		$this->setUpBackend();
		$data = 'data 1';
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, $data);
		$otherData = 'data 2';
		$this->backend->set($identifier, $otherData);
		$fetchedData = $this->backend->get($identifier);
		$this->assertSame($otherData, $fetchedData);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setOverwritesExistingEntryWithSpecifiedLifetime() {
		$this->setUpBackend();
		$this->setUpRedis();

		$data = 'data';
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, $data);
		$lifetime = 42;
		$this->backend->set($identifier, $data, array(), $lifetime);

		$lifetimeRegisteredInBackend = $this->redis->ttl('identData:' . $identifier);
		$this->assertSame($lifetime, $lifetimeRegisteredInBackend);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setOverwritesExistingEntryWithNewDefaultLifetime() {
		$this->setUpBackend();
		$this->setUpRedis();

		$data = 'data';
		$identifier = 'identifier' . uniqid();
		$lifetime = 42;
		$this->backend->set($identifier, $data, array(), $lifetime);
		$newDefaultLifetime = 43;
		$this->backend->setDefaultLifetime($newDefaultLifetime);
		$this->backend->set($identifier, $data, array(), $newDefaultLifetime);

		$lifetimeRegisteredInBackend = $this->redis->ttl('identData:' . $identifier);
		$this->assertSame($newDefaultLifetime, $lifetimeRegisteredInBackend);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setOverwritesExistingEntryWithNewUnlimitedLifetime() {
		$this->setUpBackend();
		$this->setUpRedis();

		$data = 'data';
		$identifier = 'identifier' . uniqid();
		$lifetime = 42;
		$this->backend->set($identifier, $data, array(), $lifetime);
		$this->backend->set($identifier, $data, array(), 0);

		$lifetimeRegisteredInBackend = $this->redis->ttl('identData:' . $identifier);
		$this->assertSame(31536000, $lifetimeRegisteredInBackend);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesSetDataTypeForIdentifierToTagsSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data', array('tag'));

		$this->assertSame(\Redis::REDIS_SET, $this->redis->type('identTags:' . $identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesSpecifiedTagsInIdentifierToTagsSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tags = array('thatTag', 'thisTag');
		$this->backend->set($identifier, 'data', $tags);

		$savedTags = $this->redis->sMembers('identTags:' . $identifier);
		sort($savedTags);
		$this->assertSame($tags, $savedTags);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setRemovesAllPreviouslySetTagsFromIdentifierToTagsSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tags = array('fooTag', 'barTag');
		$this->backend->set($identifier, 'data', $tags);
		$this->backend->set($identifier, 'data', array());

		$this->assertSame(array(), $this->redis->sMembers('identTags:' . $identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setRemovesMultiplePreviouslySetTagsFromIdentifierToTagsSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$firstTagSet = array('tag1', 'tag2', 'tag3', 'tag4');
		$this->backend->set($identifier, 'data', $firstTagSet);
		$secondTagSet = array('tag1', 'tag3');
		$this->backend->set($identifier, 'data', $secondTagSet);

		$actualTagSet = $this->redis->sMembers('identTags:' . $identifier);
		sort($actualTagSet);
		$this->assertSame($secondTagSet, $actualTagSet);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesSetDataTypeForTagToIdentifiersSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'tag';
		$this->backend->set($identifier, 'data', array($tag));

		$this->assertSame(\Redis::REDIS_SET, $this->redis->type('tagIdents:' . $tag));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesIdentifierInTagToIdentifiersSetOfSpecifiedTag() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'thisTag';
		$this->backend->set($identifier, 'data', array($tag));

		$savedTagToIdentifiersMemberArray = $this->redis->sMembers('tagIdents:' . $tag);
		$this->assertSame(array($identifier), $savedTagToIdentifiersMemberArray);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setAppendsSecondIdentifierInTagToIdentifiersEntry() {
		$this->setUpBackend();
		$this->setUpRedis();

		$firstIdentifier = 'identifier' . uniqid();
		$tag = 'thisTag';
		$this->backend->set($firstIdentifier, 'data', array($tag));
		$secondIdentifier = 'identifier' . uniqid();
		$this->backend->set($secondIdentifier, 'data', array($tag));

		$savedTagToIdentifiersMemberArray = $this->redis->sMembers('tagIdents:' . $tag);
		sort($savedTagToIdentifiersMemberArray);
		$identifierArray = array($firstIdentifier, $secondIdentifier);
		sort($identifierArray);
		$this->assertSame(array($firstIdentifier, $secondIdentifier), $savedTagToIdentifiersMemberArray);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setRemovesIdentifierFromTagToIdentifiersEntryIfTagIsOmittedOnConsecutiveSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'thisTag';
		$this->backend->set($identifier, 'data', array($tag));
		$this->backend->set($identifier, 'data', array());

		$savedTagToIdentifiersMemberArray = $this->redis->sMembers('tagIdents:' . $tag);
		$this->assertSame(array(), $savedTagToIdentifiersMemberArray);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setAddsIdentifierInTagToIdentifiersEntryIfTagIsAddedOnConsecutiveSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');
		$tag = 'thisTag';
		$this->backend->set($identifier, 'data', array($tag));

		$savedTagToIdentifiersMemberArray = $this->redis->sMembers('tagIdents:' . $tag);
		$this->assertSame(array($identifier), $savedTagToIdentifiersMemberArray);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesCompressedDataWithEnabledCompression() {
		$this->setUpBackend(
			array(
				'compression' => TRUE,
			)
		);
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$data = 'some data ' . microtime();
		$this->backend->set($identifier, $data);

		$uncompresedStoredData = '';
		try {
			$uncompresedStoredData = @gzuncompress($this->redis->get('identData:' . $identifier));
		} catch (\Exception $e) {
		}

		$this->assertEquals($data, $uncompresedStoredData, 'Original and compressed data don\'t match');
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function setSavesPlaintextDataWithEnabledCompressionAndCompressionLevel0() {
		$this->setUpBackend(
			array(
				'compression' => TRUE,
				'compressionLevel' => 0,
			)
		);
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$data = 'some data ' . microtime();
		$this->backend->set($identifier, $data);

		$this->assertGreaterThan(0, substr_count($this->redis->get('identData:' . $identifier), $data), 'Plaintext data not found');
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function hasThrowsExceptionIfIdentifierIsNotAString() {
		$this->setUpBackend();
		$this->backend->has(array());
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function hasReturnsFalseForNotExistingEntry() {
		$this->setUpBackend();
		$identifier = 'identifier' . uniqid();
		$this->assertFalse($this->backend->has($identifier));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function hasReturnsTrueForPreviouslySetEntry() {
		$this->setUpBackend();
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');
		$this->assertTrue($this->backend->has($identifier));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function getThrowsExceptionIfIdentifierIsNotAString() {
		$this->setUpBackend();
		$this->backend->get(array());
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function getReturnsPreviouslyCompressedSetEntry() {
		$this->setUpBackend(
			array(
				'compression' => TRUE,
			)
		);
		$data = 'data';
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, $data);
		$fetchedData = $this->backend->get($identifier);
		$this->assertSame($data, $fetchedData);
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function getReturnsPreviouslySetEntry() {
		$this->setUpBackend();
		$data = 'data';
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, $data);
		$fetchedData = $this->backend->get($identifier);
		$this->assertSame($data, $fetchedData);
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function removeThrowsExceptionIfIdentifierIsNotAString() {
		$this->setUpBackend();
		$this->backend->remove(array());
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function removeReturnsFalseIfNoEntryWasDeleted() {
		$this->setUpBackend();
		$this->assertFalse($this->backend->remove('identifier' . uniqid()));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function removeReturnsTrueIfAnEntryWasDeleted() {
		$this->setUpBackend();
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');
		$this->assertTrue($this->backend->remove($identifier));
	}

	/**
	 * @test Functional
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function removeDeletesEntryFromCache() {
		$this->setUpBackend();
		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');
		$this->backend->remove($identifier);
		$this->assertFalse($this->backend->has($identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function removeDeletesIdentifierToTagEntry() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'thisTag';
		$this->backend->set($identifier, 'data', array($tag));
		$this->backend->remove($identifier);

		$this->assertFalse($this->redis->exists('identTags:' . $identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function removeDeletesIdentifierFromTagToIdentifiersSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'thisTag';
		$this->backend->set($identifier, 'data', array($tag));
		$this->backend->remove($identifier);

		$tagToIdentifiersMemberArray = $this->redis->sMembers('tagIdents:' . $tag);
		$this->assertSame(array(), $tagToIdentifiersMemberArray);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function removeDeletesIdentifierFromTagToIdentifiersSetWithMultipleEntries() {
		$this->setUpBackend();
		$this->setUpRedis();

		$firstIdentifier = 'identifier' . uniqid();
		$secondIdentifier = 'identifier' . uniqid();
		$tag = 'thisTag';
		$this->backend->set($firstIdentifier, 'data', array($tag));
		$this->backend->set($secondIdentifier, 'data', array($tag));
		$this->backend->remove($firstIdentifier);

		$tagToIdentifiersMemberArray = $this->redis->sMembers('tagIdents:' . $tag);
		$this->assertSame(array($secondIdentifier), $tagToIdentifiersMemberArray);
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function findIdentifiersByTagThrowsExceptionIfTagIsNotAString() {
		$this->setUpBackend();
		$this->backend->findIdentifiersByTag(array());
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function findIdentifiersByTagReturnsEmptyArrayForNotExistingTag() {
		$this->setUpBackend();
		$this->assertSame(array(), $this->backend->findIdentifiersByTag('thisTag'));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function findIdentifiersByTagReturnsAllIdentifiersTagedWithSpecifiedTag() {
		$this->setUpBackend();

		$firstIdentifier = 'identifier' . uniqid();
		$secondIdentifier = 'identifier' . uniqid();
		$thirdIdentifier = 'identifier' . uniqid();
		$tagsForFirstIdentifier = array('thisTag');
		$tagsForSecondIdentifier = array('thatTag');
		$tagsForThirdIdentifier = array('thisTag', 'thatTag');

		$this->backend->set($firstIdentifier, 'data', $tagsForFirstIdentifier);
		$this->backend->set($secondIdentifier, 'data', $tagsForSecondIdentifier);
		$this->backend->set($thirdIdentifier, 'data', $tagsForThirdIdentifier);

		$expectedResult = array($firstIdentifier, $thirdIdentifier);
		$actualResult = $this->backend->findIdentifiersByTag('thisTag');
		sort($actualResult);

		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushRemovesAllEntriesFromCache() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier, 'data');
		$this->backend->flush();

		$this->assertSame(array(), $this->redis->getKeys('*'));
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 * @expectedException \InvalidArgumentException
	 */
	public function flushByTagThrowsExceptionIfTagIsNotAString() {
		$this->setUpBackend();
		$this->backend->flushByTag(array());
	}

	/**
	 * @test Functional
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushByTagRemovesEntriesTaggedWithSpecifiedTag() {
		$this->setUpBackend();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier . 'A', 'data', array('tag1'));
		$this->backend->set($identifier . 'B', 'data', array('tag2'));
		$this->backend->set($identifier . 'C', 'data', array('tag1', 'tag2'));
		$this->backend->flushByTag('tag1');

		$expectedResult = array(FALSE, TRUE, FALSE);
		$actualResult = array(
			$this->backend->has($identifier . 'A'),
			$this->backend->has($identifier . 'B'),
			$this->backend->has($identifier . 'C'),
		);
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushByTagRemovesTemporarySet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier . 'A', 'data', array('tag1'));
		$this->backend->set($identifier . 'C', 'data', array('tag1', 'tag2'));
		$this->backend->flushByTag('tag1');

		$this->assertSame(array(), $this->redis->getKeys('temp*'));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushByTagRemovesIdentifierToTagsSetOfEntryTaggedWithGivenTag() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'tag1';
		$this->backend->set($identifier, 'data', array($tag));
		$this->backend->flushByTag($tag);

		$this->assertFalse($this->redis->exists('identTags:' . $identifier));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushByTagDoesNotRemoveIdentifierToTagsSetOfUnrelatedEntry() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifierToBeRemoved = 'identifier' . uniqid();
		$tagToRemove = 'tag1';
		$this->backend->set($identifierToBeRemoved, 'data', array($tagToRemove));

		$identifierNotToBeRemoved = 'identifier' . uniqid();
		$tagNotToRemove = 'tag2';
		$this->backend->set($identifierNotToBeRemoved, 'data', array($tagNotToRemove));

		$this->backend->flushByTag($tagToRemove);

		$this->assertSame(array($tagNotToRemove), $this->redis->sMembers('identTags:' . $identifierNotToBeRemoved));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushByTagRemovesTagToIdentifiersSetOfGivenTag() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$tag = 'tag1';
		$this->backend->set($identifier, 'data', array($tag));
		$this->backend->flushByTag($tag);

		$this->assertFalse($this->redis->exists('tagIdents:' . $tag));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function flushByTagRemovesIdentifiersTaggedWithGivenTagFromTagToIdentifiersSets() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier . 'A', 'data', array('tag1', 'tag2'));
		$this->backend->set($identifier . 'B', 'data', array('tag1', 'tag2'));
		$this->backend->set($identifier . 'C', 'data', array('tag2'));

		$this->backend->flushByTag('tag1');

		$this->assertSame(array($identifier . 'C'), $this->redis->sMembers('tagIdents:tag2'));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function collectGarbageDoesNotRemoveNotExpiredIdentifierToDataEntry() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier . 'A', 'data', array('tag'));
		$this->backend->set($identifier . 'B', 'data', array('tag'));

		$this->redis->delete('identData:' . $identifier . 'A');

		$this->backend->collectGarbage();

		$this->assertTrue($this->redis->exists('identData:' . $identifier . 'B'));
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function collectGarbageRemovesLeftOverIdentifierToTagsSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier . 'A', 'data', array('tag'));
		$this->backend->set($identifier . 'B', 'data', array('tag'));

		$this->redis->delete('identData:' . $identifier . 'A');

		$this->backend->collectGarbage();

		$expectedResult = array(FALSE, TRUE);
		$actualResult = array(
			$this->redis->exists('identTags:' . $identifier . 'A'),
			$this->redis->exists('identTags:' . $identifier . 'B'),
		);
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test Implementation
	 * @author Christian Kuhn <lolli@schwarzbu.ch>
	 */
	public function collectGarbageRemovesExpiredIdentifierFromTagsToIdentifierSet() {
		$this->setUpBackend();
		$this->setUpRedis();

		$identifier = 'identifier' . uniqid();
		$this->backend->set($identifier . 'A', 'data', array('tag1', 'tag2'));
		$this->backend->set($identifier . 'B', 'data', array('tag2'));

		$this->redis->delete('identData:' . $identifier . 'A');

		$this->backend->collectGarbage();

		$expectedResult = array(
			array(),
			array($identifier . 'B')
		);
		$actualResult = array(
			$this->redis->sMembers('tagIdents:tag1'),
			$this->redis->sMembers('tagIdents:tag2'),
		);
		$this->assertSame($expectedResult, $actualResult);
	}
}
?>
