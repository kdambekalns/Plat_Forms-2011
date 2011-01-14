<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\I18n\Cldr;

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
 * Testcase for the CldrModel
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CldrModelTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\FLOW3\I18n\Cldr\CldrModel
	 */
	protected $model;

	/**
	 * @return void
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function setUp() {
		$samplePaths = array('foo', 'bar', 'baz');
		$sampleParsedFile1 = require(__DIR__ . '/../Fixtures/MockParsedCldrFile1.php');
		$sampleParsedFile2 = require(__DIR__ . '/../Fixtures/MockParsedCldrFile2.php');
		$sampleParsedFile3 = require(__DIR__ . '/../Fixtures/MockParsedCldrFile3.php');

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->once())->method('has')->with(md5('foo;bar;baz'))->will($this->returnValue(FALSE));

		$mockCldrParser = $this->getMock('F3\FLOW3\I18n\Cldr\CldrParser');
		$mockCldrParser->expects($this->at(0))->method('getParsedData')->with('foo')->will($this->returnValue($sampleParsedFile1));
		$mockCldrParser->expects($this->at(1))->method('getParsedData')->with('bar')->will($this->returnValue($sampleParsedFile2));
		$mockCldrParser->expects($this->at(2))->method('getParsedData')->with('baz')->will($this->returnValue($sampleParsedFile3));

		$this->model = new \F3\FLOW3\I18n\Cldr\CldrModel($samplePaths);
		$this->model->injectCache($mockCache);
		$this->model->injectParser($mockCldrParser);
		$this->model->initializeObject();
	}

	/**
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function mergesMultipleFilesAndResolvesAliasesCorrectly() {
		$sampleParsedFilesMerged = require(__DIR__ . '/../Fixtures/MockParsedCldrFilesMerged.php');

		$this->assertEquals($sampleParsedFilesMerged, $this->model->getRawData('/'));
	}

	/**
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function returnsRawArrayCorrectly() {
		$result = $this->model->getRawArray('dates/calendars/calendar[@type="gregorian"]/months/monthContext[@type="format"]/monthWidth[@type="abbreviated"]');
		$this->assertEquals(2, count($result));
		$this->assertEquals('jan', $result['month[@type="1"]']);
	}

	/**
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function returnsElementCorrectly() {
		$result = $this->model->getElement('localeDisplayNames/localeDisplayPattern/localePattern');
		$this->assertEquals('{0} ({1})', $result);

		$result = $this->model->getElement('localeDisplayNames/variants');
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * When the path points to a leaf, getRawArray() should return FALSE.
	 *
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function getRawArrayAlwaysReturnsArrayOrFalse() {
		$result = $this->model->getRawArray('localeDisplayNames/localeDisplayPattern/localePattern');
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function returnsNodeNameCorrectly() {
		$sampleNodeString1 = 'calendar';
		$sampleNodeString2 = 'calendar[@type="gregorian"]';

		$this->assertEquals('calendar', $this->model->getNodeName($sampleNodeString1));
		$this->assertEquals('calendar', $this->model->getNodeName($sampleNodeString2));
	}

	/**
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function returnsAttributeValueCorrectly() {
		$sampleNodeString = 'dateFormatLength[@type="medium"][@alt="proposed"]';

		$this->assertEquals('medium', $this->model->getAttributeValue($sampleNodeString, 'type'));
		$this->assertEquals('proposed', $this->model->getAttributeValue($sampleNodeString, 'alt'));
		$this->assertEquals(FALSE, $this->model->getAttributeValue($sampleNodeString, 'dateFormatLength'));
	}
}

?>