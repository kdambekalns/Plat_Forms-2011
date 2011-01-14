<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\I18n;

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
 */

/**
 * Testcase for the Locale Service class.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ServiceTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function setUp() {
		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot(new \vfsStreamDirectory('Foo'));
	}

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function returnsCorrectlyLocalizedFilename() {
		mkdir('vfs://Foo/Bar/Public/images/', 0777, TRUE);
		file_put_contents('vfs://Foo/Bar/Public/images/foobar.en.png', 'FooBar');

		$desiredLocale = new \F3\FLOW3\I18n\Locale('en_GB');
		$parentLocale = new \F3\FLOW3\I18n\Locale('en');
		$defaultLocale = new \F3\FLOW3\I18n\Locale('sv_SE');

		$filename = 'vfs://Foo/Bar/Public/images/foobar.png';
		$expectedFilename = 'vfs://Foo/Bar/Public/images/foobar.en.png';

		$mockLocaleCollection = $this->getMock('F3\FLOW3\I18n\LocaleCollection');
		$mockLocaleCollection->expects($this->once())->method('findBestMatchingLocale')->with($desiredLocale)->will($this->returnValue($desiredLocale));
		$mockLocaleCollection->expects($this->once())->method('getParentLocaleOf')->with($desiredLocale)->will($this->returnValue($parentLocale));

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\I18n\Locale', 'sv_SE')->will($this->returnValue($defaultLocale));

		$mockSettings = array('locale' => array('defaultLocaleIdentifier' => 'sv_SE'));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->once())->method('has')->with('availableLocales')->will($this->returnValue(TRUE));
		$mockCache->expects($this->once())->method('get')->with('availableLocales')->will($this->returnValue($mockLocaleCollection));

		$service = new \F3\FLOW3\I18n\Service();
		$service->injectObjectManager($mockObjectManager);
		$service->injectLocaleCollection($mockLocaleCollection);
		$service->injectSettings($mockSettings);
		$service->injectCache($mockCache);
		$service->initialize();

		$result = $service->getLocalizedFilename($filename, $desiredLocale);
		$this->assertEquals($expectedFilename, $result);
	}

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function returnsCorrectFilenameInStrictMode() {
		mkdir('vfs://Foo/Bar/Public/images/', 0777, TRUE);
		file_put_contents('vfs://Foo/Bar/Public/images/foobar.en_GB.png', 'FooBar');

		$filename = 'vfs://Foo/Bar/Public/images/foobar.png';
		$expectedFilename = 'vfs://Foo/Bar/Public/images/foobar.en_GB.png';

		$service = new \F3\FLOW3\I18n\Service();

		$result = $service->getLocalizedFilename($filename, new \F3\FLOW3\I18n\Locale('en_GB'), TRUE);
		$this->assertEquals($expectedFilename, $result);

		$result = $service->getLocalizedFilename($filename, new \F3\FLOW3\I18n\Locale('pl'), TRUE);
		$this->assertEquals($filename, $result);
	}

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function correctlyGeneratesAvailableLocales() {
		mkdir('vfs://Foo/Bar/Private/', 0777, TRUE);
		foreach (array('en', 'sr_Cyrl_RS', 'en_GB', 'sr') as $localeIdentifier) {
			file_put_contents('vfs://Foo/Bar/Private/foobar.' . $localeIdentifier . '.baz', 'FooBar');
		}

		$returnLocaleCallback = function() {
			$args = func_get_args();
			return new \F3\FLOW3\I18n\Locale($args[1]);
		};

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('create')->with('F3\FLOW3\I18n\Locale')->will($this->returnCallback($returnLocaleCallback));

		$mockPackage = $this->getMock('F3\FLOW3\Package\PackageInterface');
		$mockPackage->expects($this->any())->method('getPackageKey')->will($this->returnValue('Bar'));

		$mockPackageManager = $this->getMock('F3\FLOW3\Package\PackageManagerInterface');
		$mockPackageManager->expects($this->any())->method('getActivePackages')->will($this->returnValue(array($mockPackage)));

		$mockLocaleCollection = $this->getMock('F3\FLOW3\I18n\LocaleCollection');
		$mockLocaleCollection->expects($this->exactly(4))->method('addLocale');

		$mockSettings = array('locale' => array('defaultLocaleIdentifier' => 'sv_SE'));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->once())->method('has')->with('availableLocales')->will($this->returnValue(FALSE));

		$service = $this->getAccessibleMock('F3\FLOW3\I18n\Service', array('dummy'));
		$service->_set('localeBasePath', 'vfs://Foo/');
		$service->injectObjectManager($mockObjectManager);
		$service->injectPackageManager($mockPackageManager);
		$service->injectLocaleCollection($mockLocaleCollection);
		$service->injectSettings($mockSettings);
		$service->injectCache($mockCache);
		$service->initialize();
	}
}

?>