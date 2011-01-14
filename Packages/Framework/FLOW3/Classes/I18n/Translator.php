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
 * A class for translating messages
 *
 * Messagess (labels) can be translated in two modes:
 * - by original label: untranslated label is used as a key
 * - by ID: string identifier is used as a key (eg. user.noaccess)
 *
 * Correct plural form of translated message is returned when $quantity
 * parameter is provided to a method. Otherwise, or on failure just translated
 * version is returned (eg. when string is translated only to one form).
 *
 * When all fails, untranslated (original) string or ID is returned (depends on
 * translation method).
 *
 * Placeholders' resolving is done when needed (see FormatResolver class).
 *
 * Actual translating is done by injected TranslationProvider instance, so
 * storage format depends on concrete implementation.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @see \F3\FLOW3\I18n\FormatResolver
 * @see \F3\FLOW3\I18n\TranslationProvider\TranslationProviderInterface
 * @see \F3\FLOW3\I18n\Cldr\Reader\PluralsReader
 */
class Translator {

	/**
	 * @var \F3\FLOW3\I18n\Service
	 */
	protected $localizationService;

	/**
	 * @var \F3\FLOW3\I18n\TranslationProvider\TranslationProviderInterface
	 */
	protected $translationProvider;

	/**
	 * @var \F3\FLOW3\I18n\FormatResolver
	 */
	protected $formatResolver;

	/**
	 * @var \F3\FLOW3\I18n\Cldr\Reader\PluralsReader
	 */
	protected $pluralsReader;

	/**
	 * @param \F3\FLOW3\I18n\Service $localizationService
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectLocalizationService(\F3\FLOW3\I18n\Service $localizationService) {
		$this->localizationService = $localizationService;
	}

	/**
	 * @param \F3\FLOW3\I18n\TranslationProvider\TranslationProviderInterface $translationProvider
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectTranslationProvider(\F3\FLOW3\I18n\TranslationProvider\TranslationProviderInterface $translationProvider) {
		$this->translationProvider = $translationProvider;
	}

	/**
	 * @param \F3\FLOW3\I18n\FormatResolver $formatResolver
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectFormatResolver(\F3\FLOW3\I18n\FormatResolver $formatResolver) {
		$this->formatResolver = $formatResolver;
	}

	/**
	 * @param \F3\FLOW3\I18n\Cldr\Reader\PluralsReader $pluralsReader
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectPluralsReader(\F3\FLOW3\I18n\Cldr\Reader\PluralsReader $pluralsReader) {
		$this->pluralsReader = $pluralsReader;
	}

	/**
	 * Translates message given as $originalLabel.
	 *
	 * Searches for translation in filename defined as $sourceName. It is a
	 * relative name (interpretation depends on concrete translation provider
	 * injected to this class).
	 *
	 * If any arguments are provided in $arguments array, they will be inserted
	 * to the translated string (in place of corresponding placeholders, with
	 * format defined by these placeholders).
	 *
	 * If $quantity is provided, correct plural form for provided $locale will
	 * be chosen and used to choose correct translation variant.
	 *
	 * If no $locale is provided, default system locale will be used.
	 * 
	 * @param string $originalLabel Untranslated message
	 * @param string $sourceName Name of file with translations
	 * @param array $arguments An array of values to replace placeholders with
	 * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use (NULL for default one)
	 * @return string Translated $originalLabel or $originalLabel itself on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 */
	public function translateByOriginalLabel($originalLabel, $sourceName, array $arguments = array(), $quantity = NULL, \F3\FLOW3\I18n\Locale $locale = NULL) {
		if ($locale === NULL) {
			$locale = $this->localizationService->getDefaultLocale();
		}

		if ($quantity === NULL) {
			$pluralForm = \F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_OTHER;
		} else {
			$pluralForm = $this->pluralsReader->getPluralForm($quantity, $locale);
		}

		$translatedMessage = $this->translationProvider->getTranslationByOriginalLabel($sourceName, $originalLabel, $locale, $pluralForm);

		if ($translatedMessage === FALSE) {
				// Return original message if no translation available
			$translatedMessage = $originalLabel;
		}

		if (!empty($arguments)) {
			$translatedMessage = $this->formatResolver->resolvePlaceholders($translatedMessage, $arguments, $locale);
		}

		return $translatedMessage;
	}

	/**
	 * Returns translated string found under the key $labelId in $sourceName file.
	 *
	 * This method works same as translateByOriginalLabel(), except it uses
	 * ID, and not source message, as a key.
	 *
	 * @param string $labelId Key to use for finding translation
	 * @param string $sourceName Name of file with translations
	 * @param array $arguments An array of values to replace placeholders with
	 * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to use (NULL for default one)
	 * @return string Translated message or $labelId on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @api
	 * @see \F3\FLOW3\I18n\Translator::translateByOriginalLabel()
	 */
	public function translateById($labelId, $sourceName, array $arguments = array(), $quantity = NULL, \F3\FLOW3\I18n\Locale $locale = NULL) {
		if ($locale === NULL) {
			$locale = $this->localizationService->getDefaultLocale();
		}

		if ($quantity === NULL) {
			$pluralForm = \F3\FLOW3\I18n\Cldr\Reader\PluralsReader::RULE_OTHER;
		} else {
			$pluralForm = $this->pluralsReader->getPluralForm($quantity, $locale);
		}

		$translatedMessage = $this->translationProvider->getTranslationById($sourceName, $labelId, $locale, $pluralForm);

		if ($translatedMessage === FALSE) {
				// Return the ID if no translation available
			$translatedMessage = $labelId;
		} else if (!empty($arguments)) {
			$translatedMessage = $this->formatResolver->resolvePlaceholders($translatedMessage, $arguments, $locale);
		}

		return $translatedMessage;
	}
}

?>