<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\I18n\Cldr\Reader;

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
 * Testcase for the PluralsReader
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PluralsReaderTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\FLOW3\I18n\Cldr\Reader\PluralsReader
	 */
	protected $reader;

	/**
	 * @return void
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function setUp() {
		$samplePluralRulesData = array(
			'pluralRules[@locales="ro mo"]' => array(
				'pluralRule[@count="one"]' => 'n is 1',
				'pluralRule[@count="few"]' => 'n is 0 OR n is not 1 AND n mod 100 in 1..19',
			),
		);

		$mockModel = $this->getAccessibleMock('F3\FLOW3\I18n\Cldr\CldrModel', array('getRawArray'), array(array('fake/path')));
		$mockModel->expects($this->once())->method('getRawArray')->with('plurals')->will($this->returnValue($samplePluralRulesData));

		$mockRepository = $this->getMock('F3\FLOW3\I18n\Cldr\CldrRepository');
		$mockRepository->expects($this->once())->method('getModel')->with('supplemental/plurals')->will($this->returnValue($mockModel));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->at(0))->method('has')->with('rulesets')->will($this->returnValue(FALSE));
		$mockCache->expects($this->at(1))->method('set')->with('rulesets');
		$mockCache->expects($this->at(2))->method('set')->with('rulesetsIndices');

		$this->reader = new \F3\FLOW3\I18n\Cldr\Reader\PluralsReader();
		$this->reader->injectCldrRepository($mockRepository);
		$this->reader->injectCache($mockCache);
		$this->reader->initializeObject();
	}

	/**
	 * @test
	 * @author Karol Gusak <karol@gusak.eu>
	 */
	public function returnsCorrectPluralForm() {
		$locale = new \F3\FLOW3\I18n\Locale('mo');

		$result = $this->reader->getPluralForm(1, $locale);
		$this->assertEquals(\F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_ONE, $result);

		$result = $this->reader->getPluralForm(2, $locale);
		$this->assertEquals(\F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_FEW, $result);

		$result = $this->reader->getPluralForm(100, $locale);
		$this->assertEquals(\F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_OTHER, $result);

		$result = $this->reader->getPluralForm(101, $locale);
		$this->assertEquals(\F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_FEW, $result);

		$result = $this->reader->getPluralForm(101.1, $locale);
		$this->assertEquals(\F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_OTHER, $result);
	}
}

?>