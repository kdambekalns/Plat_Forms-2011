<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\I18n\Parser;

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
 * Testcase for the DatetimeParser
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class DatetimeParserTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\FLOW3\I18n\Locale
	 */
	protected $sampleLocale;

	/**
	 * @var array
	 */
	protected $sampleLocalizedLiterals;

	/**
	 * Template datetime attributes - expected results are merged with this
	 * array so code is less redundant.
	 *
	 * @var array
	 */
	protected $datetimeAttributesTemplate = array(
		'year' => NULL,
		'month' => NULL,
		'day' => NULL,
		'hour' => NULL,
		'minute' => NULL,
		'second' => NULL,
		'timezone' => NULL,
	);

	/**
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function setUp() {
		$this->sampleLocale = new \F3\FLOW3\I18n\Locale('en_GB');
		$this->sampleLocalizedLiterals = require(__DIR__ . '/../Fixtures/MockLocalizedLiteralsArray.php');
	}

	/**
	 * Sample data for all test methods, with format type, string datetime to
	 * parse, string format, expected parsed datetime, and parsed format.
	 *
	 * Note that this data provider has everything needed by any test method, so
	 * not every element is used by every method.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function sampleDatetimesEasyToParse() {
		return array(
			array(\F3\FLOW3\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_DATE, '1988.11.19 AD', 'yyyy.MM.dd G', array_merge($this->datetimeAttributesTemplate, array('year' => 1988, 'month' => 11, 'day' => 19)), array('yyyy', array('.'), 'MM', array('.'), 'dd', array(' '), 'G')),
			array(\F3\FLOW3\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_TIME, '10:00:59', 'HH:mm:ss', array_merge($this->datetimeAttributesTemplate, array('hour' => 10, 'minute' => 0, 'second' => 59)), array('HH', array(':'), 'mm', array(':'), 'ss')),
			array(\F3\FLOW3\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_TIME, '3 p.m. Europe/Berlin', 'h a zzzz', array_merge($this->datetimeAttributesTemplate, array('hour' => 15, 'timezone' => 'Europe/Berlin')), array('h', array(' '), 'a', array(' '),'zzzz')),
		);
	}

	/**
	 * Sample data with structure like in sampleDatetimesEasyToParse(), but with
	 * examples harder to parse - only lenient parsing mode should be able to
	 * parse them.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function sampleDatetimesHardToParse() {
		return array(
			array(\F3\FLOW3\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_DATE, 'foo 2010/07 /30th', 'y.M.d', array_merge($this->datetimeAttributesTemplate, array('year' => 2010, 'month' => 7, 'day' => 30)), array('y', array('.'), 'M', array('.'), 'd')),
			array(\F3\FLOW3\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_DATE, 'Jun foo 99 Europe/Berlin', 'MMMyyz', array_merge($this->datetimeAttributesTemplate, array('year' => 99, 'month' => 6, 'timezone' => 'Europe/Berlin')), array('MMM', 'yy', 'z')),
			array(\F3\FLOW3\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_TIME, '24:11 CEST', 'K:m zzzz', array_merge($this->datetimeAttributesTemplate, array('hour' => 0, 'minute' => 11, 'timezone' => 'CEST')), array('K', array(':'), 'm', array(' '), 'zzzz')),
		);
	}

	/**
	 * @test
	 * @dataProvider sampleDatetimesEasyToParse
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function strictParsingWorksCorrectlyForEasyDatetimes($formatType, $datetimeToParse, $stringFormat, $expectedParsedDatetime, array $parsedFormat) {
		$parser = $this->getAccessibleMock('F3\FLOW3\I18n\Parser\DatetimeParser', array('dummy'));
		$result = $parser->_call('doParsingInStrictMode', $datetimeToParse, $parsedFormat, $this->sampleLocalizedLiterals);
		$this->assertEquals($expectedParsedDatetime, $result);
	}

	/**
	 * @test
	 * @dataProvider sampleDatetimesHardToParse
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function strictParsingReturnsFalseForHardDatetimes($formatType, $datetimeToParse, $stringFormat, $expectedParsedDatetime, array $parsedFormat) {
		$parser = $this->getAccessibleMock('F3\FLOW3\I18n\Parser\DatetimeParser', array('dummy'));
		$result = $parser->_call('doParsingInStrictMode', $datetimeToParse, $parsedFormat, $this->sampleLocalizedLiterals);
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * @test
	 * @dataProvider sampleDatetimesEasyToParse
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function lenientParsingWorksCorrectlyForEasyDatetimes($formatType, $datetimeToParse, $stringFormat, $expectedParsedDatetime, array $parsedFormat) {
		$parser = $this->getAccessibleMock('F3\FLOW3\I18n\Parser\DatetimeParser', array('dummy'));
		$result = $parser->_call('doParsingInLenientMode', $datetimeToParse, $parsedFormat, $this->sampleLocalizedLiterals);
		$this->assertEquals($expectedParsedDatetime, $result);
	}

	/**
	 * @test
	 * @dataProvider sampleDatetimesHardToParse
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function lenientParsingWorksCorrectlyForHardDatetimes($formatType, $datetimeToParse, $stringFormat, $expectedParsedDatetime, array $parsedFormat) {
		$parser = $this->getAccessibleMock('F3\FLOW3\I18n\Parser\DatetimeParser', array('dummy'));
		$result = $parser->_call('doParsingInLenientMode', $datetimeToParse, $parsedFormat, $this->sampleLocalizedLiterals);
		$this->assertEquals($expectedParsedDatetime, $result);
	}
}

?>