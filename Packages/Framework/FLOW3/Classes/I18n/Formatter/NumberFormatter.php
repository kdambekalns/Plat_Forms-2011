<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n\Formatter;

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
 * Formatter for numbers.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class NumberFormatter implements \F3\FLOW3\I18n\Formatter\FormatterInterface {

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
	 * Formats provided value using optional style properties
	 *
	 * @param mixed $value Formatter-specific variable to format (can be integer, \DateTime, etc)
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use
	 * @param string $styleProperties Integer-indexed array of formatter-specific style properties (can be empty)
	 * @return string String representation of $value provided, or (string)$value
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function format($value, \F3\FLOW3\I18n\Locale $locale, array $styleProperties = array()) {
		if (isset($styleProperties[0])) {
			$formatType = $styleProperties[0];
			\F3\FLOW3\I18n\Cldr\Reader\NumbersReader::validateFormatType($formatType);
		} else {
			$formatType = \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_DECIMAL;
		}

		switch ($formatType) {
			case \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_PERCENT:
				return $this->formatPercentNumber($value, $locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT);
			default:
				return $this->formatDecimalNumber($value, $locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT);
		}
	}

	/**
	 * Returns number formatted by custom format, string provided in parameter.
	 *
	 * Format must obey syntax defined in CLDR specification, excluding
	 * unimplemented features (see documentation for this class).
	 *
	 * Format is remembered in this classes cache and won't be parsed again for
	 * some time.
	 *
	 * @param mixed $number Float or int, can be negative, can be NaN or infinite
	 * @param string $format Format string
	 * @param \F3\FLOW3\I18n\Locale $locale A locale used for finding symbols array
	 * @return string Formatted number. Will return string-casted version of $number if pattern is not valid / supported
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function formatNumberWithCustomPattern($number, $format, \F3\FLOW3\I18n\Locale $locale) {
		return $this->doFormattingWithParsedFormat($number, $this->numbersReader->parseCustomFormat($format), $this->numbersReader->getLocalizedSymbolsForLocale($locale));
	}

	/**
	 * Formats number with format string for decimal numbers defined in CLDR for
	 * particular locale.
	 *
	 * Note: currently length is not used in decimalFormats from CLDR.
	 * But it's defined in the specification, so we support it here.
	 *
	 * @param mixed $number Float or int, can be negative, can be NaN or infinite
	 * @param \F3\FLOW3\I18n\Locale $locale
	 * @param string $formatLength One of NumbersReader FORMAT_LENGTH constants
	 * @return string Formatted number. Will return string-casted version of $number if there is no pattern for given $locale / $formatLength
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function formatDecimalNumber($number, \F3\FLOW3\I18n\Locale $locale, $formatLength = \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT) {
		\F3\FLOW3\I18n\Cldr\Reader\NumbersReader::validateFormatLength($formatLength);
		return $this->doFormattingWithParsedFormat($number, $this->numbersReader->parseFormatFromCldr($locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_DECIMAL, $formatLength), $this->numbersReader->getLocalizedSymbolsForLocale($locale));
	}

	/**
	 * Formats number with format string for percentage defined in CLDR for
	 * particular locale.
	 *
	 * Note: currently length is not used in percentFormats from CLDR.
	 * But it's defined in the specification, so we support it here.
	 *
	 * @param mixed $number Float or int, can be negative, can be NaN or infinite
	 * @param \F3\FLOW3\I18n\Locale $locale
	 * @param string $formatLength One of NumbersReader FORMAT_LENGTH constants
	 * @return string Formatted number. Will return string-casted version of $number if there is no pattern for given $locale / $formatLength
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function formatPercentNumber($number, \F3\FLOW3\I18n\Locale $locale, $formatLength = \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT) {
		\F3\FLOW3\I18n\Cldr\Reader\NumbersReader::validateFormatLength($formatLength);
		return $this->doFormattingWithParsedFormat($number, $this->numbersReader->parseFormatFromCldr($locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_PERCENT, $formatLength), $this->numbersReader->getLocalizedSymbolsForLocale($locale));
	}

	/**
	 * Formats number with format string for currency defined in CLDR for
	 * particular locale.
	 *
	 * Currency symbol provided will be inserted into formatted number string.
	 *
	 * Note: currently length is not used in currencyFormats from CLDR.
	 * But it's defined in the specification, so we support it here.
	 *
	 * @param mixed $number Float or int, can be negative, can be NaN or infinite
	 * @param \F3\FLOW3\I18n\Locale $locale
	 * @param string $currency Currency symbol (or name)
	 * @param string $formatLength One of NumbersReader FORMAT_LENGTH constants
	 * @return string Formatted number. Will return string-casted version of $number if there is no pattern for given $locale / $formatLength
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function formatCurrencyNumber($number, \F3\FLOW3\I18n\Locale $locale, $currency, $formatLength = \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_DEFAULT) {
		\F3\FLOW3\I18n\Cldr\Reader\NumbersReader::validateFormatLength($formatLength);
		return $this->doFormattingWithParsedFormat($number, $this->numbersReader->parseFormatFromCldr($locale, \F3\FLOW3\I18n\Cldr\Reader\NumbersReader::FORMAT_TYPE_CURRENCY, $formatLength), $this->numbersReader->getLocalizedSymbolsForLocale($locale), $currency);
	}

	/**
	 * Formats provided float or integer.
	 *
	 * Format rules defined in $parsedFormat array are used. Localizable symbols
	 * are replaced with elelements from $symbols array, and currency
	 * placeholder is replaced with the value of $currency, if not NULL.
	 *
	 * If $number is NaN or infite, proper localized symbol will be returned,
	 * as defined in CLDR specification.
	 *
	 * @param mixed $number Float or int, can be negative, can be NaN or infinite
	 * @param array $parsedFormat An array describing format (as in $parsedFormats property)
	 * @param array $symbols An array with symbols to use (as in $localeSymbols property)
	 * @param string $currency Currency symbol to be inserted into formatted number (if applicable)
	 * @return string Formatted number. Will return string-casted version of $number if pattern is FALSE
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function doFormattingWithParsedFormat($number, array $parsedFormat, array $symbols, $currency = NULL) {
		if ($parsedFormat === FALSE) {
			return (string)$number;
		}

		if (is_nan($number)) {
			return $symbols['nan'];
		}

		if (is_infinite($number)) {
			if ($number < 0) {
				return $parsedFormat['negativePrefix'] . $symbols['infinity'] . $parsedFormat['negativeSuffix'];
			} else {
				return $parsedFormat['positivePrefix'] . $symbols['infinity'] . $parsedFormat['positiveSuffix'];
			}
		}

		$isNegative = $number < 0;
		$number = abs($number * $parsedFormat['multiplier']);

		if ($parsedFormat['rounding'] > 0) {
			$number = round($number / $parsedFormat['rounding'], 0, \PHP_ROUND_HALF_EVEN) * $parsedFormat['rounding'];
		}

		if ($parsedFormat['maxDecimalDigits'] >= 0) {
			$number = round($number, $parsedFormat['maxDecimalDigits']);
		}

		$number = (string)$number;

		if (($positionOfDecimalSeparator = strpos($number, '.')) !== FALSE) {
			$integerPart = substr($number, 0, $positionOfDecimalSeparator);
			$decimalPart = substr($number, $positionOfDecimalSeparator + 1);
		} else {
			$integerPart = $number;
			$decimalPart = '';
		}

		if ($parsedFormat['minDecimalDigits'] > strlen($decimalPart)) {
			$decimalPart = str_pad($decimalPart, $parsedFormat['minDecimalDigits'], '0');
		}

		$integerPart = str_pad($integerPart, $parsedFormat['minIntegerDigits'], '0', STR_PAD_LEFT);

		if ($parsedFormat['primaryGroupingSize'] > 0 && strlen($integerPart) > $parsedFormat['primaryGroupingSize']) {
			$primaryGroupOfIntegerPart = substr($integerPart, - $parsedFormat['primaryGroupingSize']);
			$restOfIntegerPart = substr($integerPart, 0, - $parsedFormat['primaryGroupingSize']);

				// Pad the numbers with spaces from the left, so the length of the string is a multiply of secondaryGroupingSize (and str_split() can split on equal parts)
			$padLengthToGetEvenSize = (int)((strlen($restOfIntegerPart) + $parsedFormat['secondaryGroupingSize'] - 1) / $parsedFormat['secondaryGroupingSize']) * $parsedFormat['secondaryGroupingSize'];
			$restOfIntegerPart = str_pad($restOfIntegerPart, $padLengthToGetEvenSize, ' ', STR_PAD_LEFT);

				// Insert localized group separators between every secondary groups and primary group (using str_split() and implode())
			$secondaryGroupsOfIntegerPart = str_split($restOfIntegerPart, $parsedFormat['secondaryGroupingSize']);
			$integerPart = ltrim(implode($symbols['group'], $secondaryGroupsOfIntegerPart)) . $symbols['group'] . $primaryGroupOfIntegerPart;
		}

		if (strlen($decimalPart) > 0) {
			$decimalPart = $symbols['decimal'] . $decimalPart;
		}

		if ($isNegative) {
			$number = $parsedFormat['negativePrefix'] . $integerPart . $decimalPart . $parsedFormat['negativeSuffix'];
		} else {
			$number = $parsedFormat['positivePrefix'] . $integerPart . $decimalPart . $parsedFormat['positiveSuffix'];
		}

		$number = str_replace(array('%', '‰', '-'), array($symbols['percentSign'], $symbols['perMille'], $symbols['minusSign']), $number);
		if ($currency !== NULL) {
				// @todo: When currency is set, min / max DecimalDigits and rounding is overrided with CLDR data
			$number = str_replace('¤', $currency, $number);
		}

		return $number;
	}
}

?>