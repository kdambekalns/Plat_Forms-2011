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
 * Testcase for the Locale Utility
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class UtilityTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * Data provider with valid Accept-Language headers and expected results.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function sampleHttpAcceptLanguageHeaders() {
		return array(
			array('pl, en-gb;q=0.8, en;q=0.7', array('pl', 'en-gb', 'en')),
			array('de, *;q=0.8', array('de', '*')),
			array('sv, wont-accept;q=0.8, en;q=0.5', array('sv', 'en')),
		);
	}

	/**
	 * @test
	 * @dataProvider sampleHttpAcceptLanguageHeaders
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function httpAcceptLanguageHeadersAreParsedCorrectly($acceptLanguageHeader, array $expectedResult) {
		$languages = \F3\FLOW3\I18n\Utility::parseAcceptLanguageHeader($acceptLanguageHeader);
		$this->assertEquals($expectedResult, $languages);
	}

	/**
	 * Data provider with filenames with locale tags and expected results.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function filenamesWithLocale() {
		return array(
			array('/foo/bar/foobar.en_GB.ext', 'en_GB'),
			array('/foo/bar/foobar.ext', FALSE),
			array('/foo/bar/foobar', FALSE),
		);
	}

	/**
	 * @test
	 * @dataProvider filenamesWithLocale
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function localeIdentifiersAreCorrectlyExtractedFromFilename($filename, $expectedResult) {
		$result = \F3\FLOW3\I18n\Utility::extractLocaleTagFromFilename($filename);
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * Data provider with haystack strings and needle strings, used to test
	 * comparison methods. The third argument denotes whether needle is same
	 * as beginning of the haystack, or it's ending, or both or none.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function sampleHaystackStringsAndNeedleStrings() {
		return array(
			array('teststring', 'test', 'beginning'),
			array('foo', 'bar', 'none'),
			array('baz', '', 'none'),
			array('foo', 'foo', 'both'),
			array('foobaz', 'baz', 'ending'),
		);
	}

	/**
	 * @test
	 * @dataProvider sampleHaystackStringsAndNeedleStrings
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function stringIsFoundAtBeginningOfAnotherString($haystack, $needle, $comparison) {
		$expectedResult = ($comparison === 'beginning' || $comparison === 'both') ? TRUE : FALSE;
		$result = \F3\FLOW3\I18n\Utility::stringBeginsWith($haystack, $needle);
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * @test
	 * @dataProvider sampleHaystackStringsAndNeedleStrings
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function stringIsFoundAtEndingOfAnotherString($haystack, $needle, $comparison) {
		$expectedResult = ($comparison === 'ending' || $comparison === 'both') ? TRUE : FALSE;
		$result = \F3\FLOW3\I18n\Utility::stringEndsWith($haystack, $needle);
		$this->assertEquals($expectedResult, $result);
	}
}

?>