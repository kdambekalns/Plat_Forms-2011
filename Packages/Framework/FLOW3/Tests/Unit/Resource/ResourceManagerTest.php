<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Resource;

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
 * Testcase for the resource manager
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ResourceManagerTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setUp() {
		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot(new \vfsStreamDirectory('Foo'));
	}

	/**
	 * This test indeed messes with some of the static stuff concerning our
	 * StreamWrapperAdapter setup. But since the dummy stream wrapper is removed again,
	 * this does not do any harm. And registering the "real" wrappers a second
	 * time doesn't do harm, either.
	 *
	 * What is an issue is the static object manager being set to a mocked one,
	 * be careful...
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function initializeRegistersFoundStreamWrappers() {
		$wrapperClassName = uniqid('MockWrapper');
		$wrapperSchemeName = $wrapperClassName . 'scheme';
		eval('class ' . $wrapperClassName . ' extends \F3\FLOW3\Resource\Streams\ResourceStreamWrapper { static public function getScheme() { return \'' . $wrapperSchemeName . '\'; } }');
		$mockStreamWrapperAdapter = new $wrapperClassName();

		$streamWrapperAdapterReflection = new \ReflectionClass('F3\FLOW3\Resource\Streams\StreamWrapperAdapter');
		$property = $streamWrapperAdapterReflection->getProperty('objectManager');
		$property->setAccessible(TRUE);
		$originalObjectManager = $property->getValue();

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');

		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getAllImplementationClassNamesForInterface')->with('F3\FLOW3\Resource\Streams\StreamWrapperInterface')->will($this->returnValue(array(get_class($mockStreamWrapperAdapter))));

		$resourceManager = new \F3\FLOW3\Resource\ResourceManager();
		$resourceManager->injectObjectManager($mockObjectManager);
		$resourceManager->injectReflectionService($mockReflectionService);
		$resourceManager->initialize();

		$this->assertContains(get_class($mockStreamWrapperAdapter), \F3\FLOW3\Resource\Streams\StreamWrapperAdapter::getRegisteredStreamWrappers());
		$this->assertArrayHasKey($wrapperSchemeName, \F3\FLOW3\Resource\Streams\StreamWrapperAdapter::getRegisteredStreamWrappers());
		$this->assertContains($wrapperSchemeName, stream_get_wrappers());
		stream_wrapper_unregister($wrapperSchemeName);

			// set the real object factory again...
		if ($originalObjectManager !== NULL) {
			\F3\FLOW3\Resource\Streams\StreamWrapperAdapter::injectObjectManager($originalObjectManager);
		}
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function publishPublicPackageResourcesPublishesStaticResourcesOfActivePackages() {
		$settings = array('resource' => array('publishing' => array('detectPackageResourceChanges' => TRUE)));

		$mockStatusCache = $this->getMock('F3\FLOW3\Cache\Frontend\StringFrontend', array(), array(), '', FALSE);
		$mockStatusCache->expects($this->once())->method('set')->with('packageResourcesPublished', 'y', array(\F3\FLOW3\Cache\Frontend\FrontendInterface::TAG_PACKAGE));

		$mockPackage = $this->getMock('F3\FLOW3\Package\PackageInterface', array(), array(), '', FALSE);
		$mockPackage->expects($this->exactly(2))->method('getResourcesPath')->will($this->onConsecutiveCalls('Packages/Foo/Resources/', 'Packages/Bar/Resources/'));

		$mockResourcePublisher = $this->getMock('F3\FLOW3\Resource\Publishing\ResourcePublisher', array(), array(), '', FALSE);
		$mockResourcePublisher->expects($this->at(0))->method('publishStaticResources')->with('Packages/Foo/Resources/Public/', 'Packages/Foo/');
		$mockResourcePublisher->expects($this->at(1))->method('publishStaticResources')->with('Packages/Bar/Resources/Public/', 'Packages/Bar/');


		$resourceManager = new \F3\FLOW3\Resource\ResourceManager();
		$resourceManager->injectResourcePublisher($mockResourcePublisher);
		$resourceManager->injectStatusCache($mockStatusCache);
		$resourceManager->injectSettings($settings);

		$resourceManager->publishPublicPackageResources(array('Foo' => $mockPackage, 'Bar' => $mockPackage));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function publishPublicPackageResourcesStoresThePublishingStatusInACacheDoesntPublishResourcesAgainIfSettingsSaySo() {
		$settings = array('resource' => array('publishing' => array('detectPackageResourceChanges' => FALSE)));

		$mockStatusCache = $this->getMock('F3\FLOW3\Cache\Frontend\StringFrontend', array(), array(), '', FALSE);
		$mockStatusCache->expects($this->once())->method('has')->with('packageResourcesPublished')->will($this->returnValue(TRUE));

		$mockPackage = $this->getMock('F3\FLOW3\Package\PackageInterface', array(), array(), '', FALSE);

		$mockResourcePublisher = $this->getMock('F3\FLOW3\Resource\Publishing\ResourcePublisher', array(), array(), '', FALSE);
		$mockResourcePublisher->expects($this->never())->method('publishStaticResource');


		$resourceManager = new \F3\FLOW3\Resource\ResourceManager();
		$resourceManager->injectResourcePublisher($mockResourcePublisher);
		$resourceManager->injectStatusCache($mockStatusCache);
		$resourceManager->injectSettings($settings);

		$resourceManager->publishPublicPackageResources(array('Foo' => $mockPackage, 'Bar' => $mockPackage));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPersistentResourcesStorageBaseUriProvidesTheUriAtAWellKnownPlace() {
		$resourceManager = $this->getAccessibleMock('\F3\FLOW3\Resource\ResourceManager', array('dummy'), array(), '', FALSE);
		$resourceManager->_set('persistentResourcesStorageBaseUri', 'vfs://Foo/Bar/');

		$actualUri = $resourceManager->getPersistentResourcesStorageBaseUri();
		$this->assertSame('vfs://Foo/Bar/', $actualUri);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 *
	 */
	public function importResourceImportsTheGivenFileAndReturnsAResourceObject() {
		file_put_contents('vfs://Foo/SomeResource.txt', '12345');
		$hash = sha1_file('vfs://Foo/SomeResource.txt');

		mkdir('vfs://Foo/Temporary');
		mkdir('vfs://Foo/Persistent');
		mkdir('vfs://Foo/Persistent/Resources');

		$mockResourcePointer = $this->getMock('F3\FLOW3\Resource\ResourcePointer', array(), array(), '', FALSE);

		$mockResource = $this->getMock('F3\FLOW3\Resource\Resource', array(), array(), '', FALSE);
		$mockResource->expects($this->once())->method('setFilename')->with('SomeResource.txt');
		$mockResource->expects($this->once())->method('setResourcePointer')->with($mockResourcePointer);

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->at(0))->method('create')->with('F3\FLOW3\Resource\Resource')->will($this->returnValue($mockResource));
		$mockObjectManager->expects($this->at(1))->method('create')->with('F3\FLOW3\Resource\ResourcePointer', $hash)->will($this->returnValue($mockResourcePointer));
		$mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment');
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/Temporary/'));

		$resourceManager = $this->getAccessibleMock('\F3\FLOW3\Resource\ResourceManager', array('dummy'), array(), '', FALSE);
		$resourceManager->_set('persistentResourcesStorageBaseUri', 'vfs://Foo/Persistent/Resources/');
		$resourceManager->_set('importedResources', new \SplObjectStorage());
		$resourceManager->injectObjectManager($mockObjectManager);
		$resourceManager->injectEnvironment($mockEnvironment);

		$actualResource = $resourceManager->importResource('vfs://Foo/SomeResource.txt');
		$this->assertSame($mockResource, $actualResource);
		$this->assertFileEquals('vfs://Foo/SomeResource.txt', 'vfs://Foo/Persistent/Resources/' . $hash);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getImportedResourcesReturnsAListOfResourceObjectsAndSomeInformationAboutTheirImport() {
		file_put_contents('vfs://Foo/SomeResource.txt', '12345');
		$hash = sha1_file('vfs://Foo/SomeResource.txt');

		mkdir('vfs://Foo/Temporary');
		mkdir('vfs://Foo/Persistent');
		mkdir('vfs://Foo/Persistent/Resources');

		$mockResourcePointer = $this->getMock('F3\FLOW3\Resource\ResourcePointer', array(), array(), '', FALSE);

		$mockResource = $this->getMock('F3\FLOW3\Resource\Resource', array(), array(), '', FALSE);
		$mockResource->expects($this->once())->method('setFilename')->with('SomeResource.txt');
		$mockResource->expects($this->once())->method('setResourcePointer')->with($mockResourcePointer);

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->at(0))->method('create')->with('F3\FLOW3\Resource\Resource')->will($this->returnValue($mockResource));
		$mockObjectManager->expects($this->at(1))->method('create')->with('F3\FLOW3\Resource\ResourcePointer', $hash)->will($this->returnValue($mockResourcePointer));
		$mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment');
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/Temporary/'));

		$resourceManager = $this->getAccessibleMock('\F3\FLOW3\Resource\ResourceManager', array('dummy'), array(), '', FALSE);
		$resourceManager->_set('persistentResourcesStorageBaseUri', 'vfs://Foo/Persistent/Resources/');
		$resourceManager->_set('importedResources', new \SplObjectStorage());
		$resourceManager->injectObjectManager($mockObjectManager);
		$resourceManager->injectEnvironment($mockEnvironment);

		$resourceManager->importResource('vfs://Foo/SomeResource.txt');
		$importedResources = $resourceManager->getImportedResources();

		$this->assertSame('SomeResource.txt', $importedResources[$mockResource]['originalFilename']);
	}
}

?>