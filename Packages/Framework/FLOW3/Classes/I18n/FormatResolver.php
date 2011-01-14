<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n;

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
 * A class for replacing placeholders in strings with formatted values.
 *
 * Placeholders have following syntax:
 * {id[,name[,attribute1[,attribute2...]]]}
 *
 * Where 'id' is an index of argument to insert in place of placeholder, an
 * optional 'name' is a name of formatter to use for formatting the argument
 * (if no name given, provided argument will be just string-casted), and
 * optional attributes are strings directly passed to the formatter (what they
 * do depends on concrete formatter which is being used).
 *
 * Examples:
 * {0}
 * {0,number,decimal}
 * {1,datetime,time,full}
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class FormatResolver {

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\I18n\Service
	 */
	protected $localizationService;

	/**
	 * Array of concrete formatters used by this class.
	 *
	 * @var array<\F3\FLOW3\I18n\Formatter\FormatterInterface>
	 */
	protected $formatters;

	/**
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \F3\FLOW3\I18n\Service $localizationService
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectLocalizationService(\F3\FLOW3\I18n\Service $localizationService) {
		$this->localizationService = $localizationService;
	}

	/**
	 * Replaces all placeholders in text with corresponding values.
	 *
	 * A placeholder is a group of elements separated with comma. First element
	 * is required and defines index of value to insert (numeration starts from
	 * 0, and is directly used to access element from $values array). Second
	 * element is a name of formatter to use. It's optional, and if not given,
	 * value will be simply string-casted. Remaining elements are formatter-
	 * specific and they are directly passed to the formatter class.
	 *
	 * @param string $textWithPlaceholders String message with placeholder(s)
	 * @param array $arguments An array of values to replace placeholders with
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use (NULL for default one)
	 * @return string The $text with placeholders resolved
	 * @throws \F3\FLOW3\I18n\Exception\InvalidFormatPlaceholderException When encountered incorrectly formatted placeholder
	 * @throws \F3\FLOW3\I18n\Exception\IndexOutOfBoundsException When trying to format nonexistent value
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function resolvePlaceholders($textWithPlaceholders, array $arguments, \F3\FLOW3\I18n\Locale $locale = NULL) {
		if ($locale === NULL) {
			$locale = $this->localizationService->getDefaultLocale();
		}

		while (($startOfPlaceholder = strpos($textWithPlaceholders, '{')) !== FALSE) {
			$endOfPlaceholder = strpos($textWithPlaceholders, '}');
			$startOfNextPlaceholder = strpos($textWithPlaceholders, '{', $startOfPlaceholder + 1);

			if ($endOfPlaceholder === FALSE || ($startOfPlaceholder + 1) >= $endOfPlaceholder || ($startOfNextPlaceholder !== FALSE && $startOfNextPlaceholder < $endOfPlaceholder)) {
					// There is no closing bracket, or it is placed before the opening bracket, or there is nothing between brackets
				throw new \F3\FLOW3\I18n\Exception\InvalidFormatPlaceholderException('Text provided contains incorrectly formatted placeholders. Please make sure you conform the placeholder\'s syntax.', 1278057790);
			}

			$contentBetweenBrackets = substr($textWithPlaceholders, $startOfPlaceholder + 1, $endOfPlaceholder - $startOfPlaceholder - 1);
			$placeholderElements = explode(',', str_replace(' ', '', $contentBetweenBrackets));

			$valueIndex = (int)$placeholderElements[0];
			if ($valueIndex < 0 || $valueIndex >= count($arguments)) {
				throw new \F3\FLOW3\I18n\Exception\IndexOutOfBoundsException('Placeholder has incorrect index or not enough values provided. Please make sure you try to access existing values.', 1278057791);
			}

			if (isset($placeholderElements[1])) {
				$formatterName = $placeholderElements[1];
				$formatter = $this->getFormatter($formatterName);
				$formattedPlaceholder = $formatter->format($arguments[$valueIndex], $locale, array_slice($placeholderElements, 2));
			} else {
					// No formatter defined, just string-cast the value
				$formattedPlaceholder = (string)($arguments[$valueIndex]);
			}

			$textWithPlaceholders = str_replace('{' . $contentBetweenBrackets . '}', $formattedPlaceholder, $textWithPlaceholders);
		}

		return $textWithPlaceholders;
	}

	/**
	 * Returns instance of concrete formatter.
	 *
	 * The name provided has to be a name of existing class placed in
	 * \F3\FLOW3\I18n\Formatter package and implementing FormatterIntefrace
	 * (also in this package). For example,  when $formatterName is 'number',
	 * the \F3\FLOW3\I18n\Formatter\NumberFormatter class has to exist.
	 *
	 * Throws exception if there is no formatter for name given.
	 *
	 * @param string $formatterName Name of the formatter class (without Formatter suffix)
	 * @return \F3\FLOW3\I18n\Formatter\FormatterInterface The concrete formatter class
	 * @throws \F3\FLOW3\I18n\Exception\UnknownFormatterException When formatter for a name given does not exist
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function getFormatter($formatterName) {
		$formatterName = ucfirst($formatterName);

		if (isset($this->formatters[$formatterName])) {
			return $this->formatters[$formatterName];
		}

		try {
			$formatter = $this->objectManager->get('F3\\FLOW3\\I18n\\Formatter\\' . $formatterName . 'Formatter');
		} catch (\F3\FLOW3\Object\Exception\UnknownObjectException $exception) {
			throw new \F3\FLOW3\I18n\Exception\UnknownFormatterException('Could not find formatter for "' . $formatterName . '".', 1278057791);
		}

		return $this->formatters[$formatterName] = $formatter;
	}
}

?>