<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Package\MetaData;

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
 * Testcase for the XML MetaData reader
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class XmlReaderTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * Test the actual reading of a Package.xml file. This test
	 * uses the TestPackage as a fixture
	 *
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function readPackageMetaDataReadsPackageXml() {
		$mockPackage = $this->getMock('F3\FLOW3\Package\PackageInterface');

		$mockPackage->expects($this->atLeastOnce())
			->method('getMetaPath')
			->will($this->returnValue(__DIR__ . '/../Fixtures/XmlReaderTest/'));

		$mockPackage->expects($this->any())
			->method('getPackageKey')
			->will($this->returnValue('YetAnotherTestPackage'));

		$metaReader = new \F3\FLOW3\Package\MetaData\XmlReader();

		$packageMetaData = $metaReader->readPackageMetaData($mockPackage);

		$this->assertEquals('YetAnotherTestPackage', $packageMetaData->getPackageKey());
		$this->assertEquals('Yet another test package', $packageMetaData->getTitle());
		$this->assertEquals('0.1.1', $packageMetaData->getVersion());
		$this->assertEquals('A test package to test the creation of the Package.xml by the Package Manager', $packageMetaData->getDescription());
		$this->assertEquals(array('Testing', 'System'), $packageMetaData->getCategories());

		$parties = $packageMetaData->getParties();
		$this->assertTrue(is_array($parties));
		$person1 = $parties[0];
		$this->assertType('F3\FLOW3\Package\MetaData\Person', $person1);
		$this->assertEquals('LeadDeveloper', $person1->getRole());
		$this->assertEquals('Robert Lemke', $person1->getName());
		$this->assertEquals('robert@typo3.org', $person1->getEmail());

		$constraints = $packageMetaData->getConstraintsByType('depends');
		$this->assertTrue(is_array($constraints));

		$this->assertType('F3\FLOW3\Package\MetaData\PackageConstraint', $constraints[0]);
		$this->assertEquals('depends', $constraints[0]->getConstraintType());
		$this->assertEquals('FLOW3', $constraints[0]->getValue());
		$this->assertEquals('1.0.0', $constraints[0]->getMinVersion());
		$this->assertEquals('1.9.9', $constraints[0]->getMaxVersion());
		$this->assertType('F3\FLOW3\Package\MetaData\SystemConstraint', $constraints[1]);
		$this->assertNull($constraints[1]->getValue());
		$this->assertEquals('PHP', $constraints[1]->getType());
		$this->assertEquals('5.3.0', $constraints[1]->getMinVersion());
	}

}
?>