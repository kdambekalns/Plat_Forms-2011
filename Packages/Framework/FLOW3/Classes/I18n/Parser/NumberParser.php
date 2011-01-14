<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n\Parser;

/*
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
 * Parser for numbers.
 *
 * This parser does not support full syntax of number formats as defined in
 * CLDR. It uses parsed formats from NumbersReader class.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @see \F3\FLOW3\I18n\Cldr\Reader\NumbersReader
 * @api
 * @todo Currency support
 */
class NumberParser {

	/**
	 * Regex pattern for matching one or more digits.
	 */
	const PATTERN_MATCH_DIGITS = '/^[0-9]+$/';

	/**
	 * Regex pattern for matching all except digits. It's used for clearing
	 * string in lenient mode.
	 */
	const PATTERN_MATCH_NOT_DIGITS = '/[^0-9]+/';

	/**
	 * @var \F3\FLOW3\I18n\Cldr\Reader\NumbersReader
	 */
	protected $numbersReader;

	/**
	 * @param \F3\FLOW3\I18n\Cldr\Reader\NumbersReader $numbersReader
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectNumbersReader(\F3\FLOW3\I18n\Cldr\Reader\NumbersReader $numbersReader) {
		$this->numbersReader = $numbersReader;
	}

	/**
	 * Parses number given as a string using provided format.
	 *
	 * @param string $numberToParse Number to be parsed
	 * @param string $format Number format to use
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use
	 * @param bool $strictMode Work mode (strict when TRUE, lenient when FALSE)
	 * @return mixed Parsed float number or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function parseNumberWithCustomPattern($numberToParse, $format, \F3\FLOW3\I18n\Locale $locale, $strictMode = TRUE) {
		return $this->doParsingWithParsedFormat($numberToParse, $this->numbersReader->parseCustomFormat($format), $this->numbersReader->getLocalizedSymbolsForLocale($locale), $strictMode);
	}

	/**
	 * Parses decimal number using proper format from CLDR.
	 *
	 * @param string $numberToParse Number to be parsed
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use
	 * @param string $formatLength One of NumbersReader FORMAT_LENGTH constants
	 * @param bool $strictMode Work mode (strict when TRUE, lenient when FALSE)
	 * @return mixed Parsed float number or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function parseDecimalNumber($numberToParse, \F3\FLOW3\I18n\Locale $locale, $formatLength = \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT, $strictMode = TRUE) {
		\F3\FLOW3\I18n\Cldr\Reader\NumbersReader::validateFormatLength($formatLength);
		return $this->doParsingWithParsedFormat($numberToParse, $this->numbersReader->parseFormatFromCldr($locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_DECIMAL, $formatLength), $this->numbersReader->getLocalizedSymbolsForLocale($locale), $strictMode);
	}

	/**
	 * Parses percent number using proper format from CLDR.
	 *
	 * @param string $numberToParse Number to be parsed
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use
	 * @param string $formatLength One of NumbersReader FORMAT_LENGTH constants
	 * @param bool $strictMode Work mode (strict when TRUE, lenient when FALSE)
	 * @return mixed Parsed float number or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function parsePercentNumber($numberToParse, \F3\FLOW3\I18n\Locale $locale, $formatLength = \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT, $strictMode = TRUE) {
		\F3\FLOW3\I18n\Cldr\Reader\NumbersReader::validateFormatLength($formatLength);
		return $this->doParsingWithParsedFormat($numberToParse, $this->numbersReader->parseFormatFromCldr($locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_PERCENT, $formatLength), $this->numbersReader->getLocalizedSymbolsForLocale($locale), $strictMode);
	}

	/**
	 * Parses number using parsed format, in strict or lenient mode.
	 *
	 * @param string $numberToParse Number to be parsed
	 * @param array $parsedFormat Parsed format (from NumbersReader)
	 * @param array $localizedSymbols An array with symbols to use
	 * @param bool $strictMode Work mode (strict when TRUE, lenient when FALSE)
	 * @return mixed Parsed float number or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function doParsingWithParsedFormat($numberToParse, array $parsedFormat, array $localizedSymbols, $strictMode) {
		return ($strictMode) ? $this->doParsingInStrictMode($numberToParse, $parsedFormat, $localizedSymbols) : $this->doParsingInLenientMode($numberToParse, $parsedFormat, $localizedSymbols);
	}

	/**
	 * Parses number in strict mode.
	 *
	 * In strict mode parser checks all constraints of provided parsed format,
	 * and if any of them is not fullfiled, parsing fails (FALSE is returned).
	 *
	 * @param string $numberToParse Number to be parsed
	 * @param array $parsedFormat Parsed format (from NumbersReader)
	 * @param array $localizedSymbols An array with symbols to use
	 * @return mixed Parsed float number or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function doParsingInStrictMode($numberToParse, array $parsedFormat, array $localizedSymbols) {
		$numberIsNegative = FALSE;

		if (!empty($parsedFormat['negativePrefix']) && !empty($parsedFormat['negativeSuffix'])) {
			if (\F3\FLOW3\I18n\Utility::stringBeginsWith($numberToParse, $parsedFormat['negativePrefix']) && \F3\FLOW3\I18n\Utility::stringEndsWith($numberToParse, $parsedFormat['negativeSuffix'])) {
				$numberToParse = substr($numberToParse, strlen($parsedFormat['negativePrefix']), - strlen($parsedFormat['negativeSuffix']));
				$numberIsNegative = TRUE;
			}
		} elseif (!empty($parsedFormat['negativePrefix']) && \F3\FLOW3\I18n\Utility::stringBeginsWith($numberToParse, $parsedFormat['negativePrefix'])) {
			$numberToParse = substr($numberToParse, strlen($parsedFormat['negativePrefix']));
			$numberIsNegative = TRUE;
		} elseif (!empty($parsedFormat['negativeSuffix']) && \F3\FLOW3\I18n\Utility::stringEndsWith($numberToParse, $parsedFormat['negativeSuffix'])) {
			$numberToParse = substr($numberToParse, 0, - strlen($parsedFormat['negativeSuffix']));
			$numberIsNegative = TRUE;
		}

		if (!$numberIsNegative) {
			if (!empty($parsedFormat['positivePrefix']) && !empty($parsedFormat['positiveSuffix'])) {
				if (\F3\FLOW3\I18n\Utility::stringBeginsWith($numberToParse, $parsedFormat['positivePrefix']) && \F3\FLOW3\I18n\Utility::stringEndsWith($numberToParse, $parsedFormat['positiveSuffix'])) {
					$numberToParse = substr($numberToParse, strlen($parsedFormat['positivePrefix']), - strlen($parsedFormat['positiveSuffix']));
				} else {
					return FALSE;
				}
			} elseif (!empty($parsedFormat['positivePrefix'])) {
				if (\F3\FLOW3\I18n\Utility::stringBeginsWith($numberToParse, $parsedFormat['positivePrefix'])) {
					$numberToParse = substr($numberToParse, strlen($parsedFormat['positivePrefix']));
				} else {
					return FALSE;
				}
			} elseif (!empty($parsedFormat['positiveSuffix'])) {
				if (\F3\FLOW3\I18n\Utility::stringEndsWith($numberToParse, $parsedFormat['positiveSuffix'])) {
					$numberToParse = substr($numberToParse, 0, - strlen($parsedFormat['positiveSuffix']));
				} else {
					return FALSE;
				}
			}
		}

		$positionOfDecimalSeparator = strpos($numberToParse, $localizedSymbols['decimal']);
		if ($positionOfDecimalSeparator === FALSE) {
			$numberToParse = str_replace($localizedSymbols['group'], '', $numberToParse);

			if (strlen($numberToParse) < $parsedFormat['minIntegerDigits']) {
				return FALSE;
			} elseif (preg_match(self::PATTERN_MATCH_DIGITS, $numberToParse, $matches) === 0) {
				return FALSE;
			}

			$integerPart = $numberToParse;
			$decimalPart = FALSE;
		} else {
			if ($positionOfDecimalSeparator === 0 && $positionOfDecimalSeparator === strlen($numberToParse) - 1) {
				return FALSE;
			}

			$numberToParse = str_replace(array($localizedSymbols['group'], $localizedSymbols['decimal']), array('', '.'), $numberToParse);

			$positionOfDecimalSeparator = strpos($numberToParse, '.');
			$integerPart = substr($numberToParse, 0, $positionOfDecimalSeparator);
			$decimalPart = substr($numberToParse, $positionOfDecimalSeparator + 1);
		}

		if (strlen($integerPart) < $parsedFormat['minIntegerDigits']) {
			return FALSE;
		} elseif (preg_match(self::PATTERN_MATCH_DIGITS, $integerPart, $matches) === 0) {
			return FALSE;
		}

		$parsedNumber = (int)$integerPart;

		if ($decimalPart !== FALSE) {
			$countOfDecimalDigits = strlen($decimalPart);
			if ($countOfDecimalDigits < $parsedFormat['minDecimalDigits'] || $countOfDecimalDigits > $parsedFormat['maxDecimalDigits']) {
				return FALSE;
			} elseif (preg_match(self::PATTERN_MATCH_DIGITS, $decimalPart, $matches) === 0) {
				return FALSE;
			}

			$parsedNumber = (float)($integerPart . '.' . $decimalPart);
		}

		$parsedNumber /= $parsedFormat['multiplier'];

		if ($parsedFormat['rounding'] !== 0.0 && ($parsedNumber - (int)($parsedNumber / $parsedFormat['rounding']) * $parsedFormat['rounding']) !== 0.0) {
			return FALSE;
		}

		if ($numberIsNegative) {
			$parsedNumber = 0 - $parsedNumber;
		}

		return $parsedNumber;
	}

	/**
	 * Parses number in lenient mode.
	 *
	 * Lenient parsing ignores everything that can be ignored, and tries to
	 * extract number from the string, even if it's not well formed.
	 *
	 * Implementation is simple but should work more often than strict parsing.
	 *
	 * Algorithm:
	 * 1. Find first digit
	 * 2. Find last digit
	 * 3. Find decimal separator between first and last digit (if any)
	 * 4. Remove non-digits from integer part
	 * 5. Remove non-digits from decimal part (optional)
	 * 6. Try to match negative prefix before first digit
	 * 7. Try to match negative suffix after last digit
	 *
	 * @param string $numberToParse Number to be parsed
	 * @param array $parsedFormat Parsed format (from NumbersReader)
	 * @param array $localizedSymbols An array with symbols to use
	 * @return mixed Parsed float number or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function doParsingInLenientMode($numberToParse, array $parsedFormat, array $localizedSymbols) {
		$numberIsNegative = FALSE;
		$positionOfFirstDigit = NULL;
		$positionOfLastDigit = NULL;

		$charactersOfNumberString = str_split($numberToParse);
		foreach($charactersOfNumberString as $position => $character) {
			if (ord($character) >= 48 && ord($character) <= 57) {
				$positionOfFirstDigit = $position;
				break;
			}
		}

		if ($positionOfFirstDigit === NULL) {
			return FALSE;
		}

		krsort($charactersOfNumberString);
		foreach($charactersOfNumberString as $position => $character) {
			if (ord($character) >= 48 && ord($character) <= 57) {
				$positionOfLastDigit = $position;
				break;
			}
		}

		$positionOfDecimalSeparator = strrpos($numberToParse, $localizedSymbols['decimal'], $positionOfFirstDigit);
		if ($positionOfDecimalSeparator === FALSE) {
			$integerPart = substr($numberToParse, $positionOfFirstDigit, $positionOfLastDigit - $positionOfFirstDigit + 1);
			$decimalPart = FALSE;
		} else {
			$integerPart = substr($numberToParse, $positionOfFirstDigit, $positionOfDecimalSeparator - $positionOfFirstDigit);
			$decimalPart = substr($numberToParse, $positionOfDecimalSeparator + 1, $positionOfLastDigit - $positionOfDecimalSeparator);
		}

		$parsedNumber = (int)preg_replace(self::PATTERN_MATCH_NOT_DIGITS, '', $integerPart);

		if ($decimalPart !== FALSE) {
			$decimalPart = (int)preg_replace(self::PATTERN_MATCH_NOT_DIGITS, '', $decimalPart);
			$parsedNumber = (float)($parsedNumber . '.' . $decimalPart);
		}

		$partBeforeNumber = substr($numberToParse, 0, $positionOfFirstDigit);
		$partAfterNumber = substr($numberToParse, - (strlen($numberToParse) - $positionOfLastDigit - 1));

		if (!empty($parsedFormat['negativePrefix']) && !empty($parsedFormat['negativeSuffix'])) {
			if (\F3\FLOW3\I18n\Utility::stringEndsWith($partBeforeNumber, $parsedFormat['negativePrefix']) && \F3\FLOW3\I18n\Utility::stringBeginsWith($partAfterNumber, $parsedFormat['negativeSuffix'])) {
				$numberIsNegative = TRUE;
			}
		} elseif (!empty($parsedFormat['negativePrefix']) && \F3\FLOW3\I18n\Utility::stringEndsWith($partBeforeNumber, $parsedFormat['negativePrefix'])) {
			$numberIsNegative = TRUE;
		} elseif (!empty($parsedFormat['negativeSuffix']) && \F3\FLOW3\I18n\Utility::stringBeginsWith($partAfterNumber, $parsedFormat['negativeSuffix'])) {
			$numberIsNegative = TRUE;
		}

		$parsedNumber /= $parsedFormat['multiplier'];

		if ($numberIsNegative) {
			$parsedNumber = 0 - $parsedNumber;
		}

		return $parsedNumber;
	}
}

?>