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
 * The LocaleCollection class contains all locales available in current
 * FLOW3 installation, and describes hierarchical relations between them.
 *
 * This class maintans a hierarchical relation between locales. For
 * example, a locale "en_GB" will be a child of a locale "en".
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class LocaleCollection {

	/**
	 * This array contains all locales added to this collection.
	 *
	 * The values are Locale objects, and the keys are these locale's tags.
	 *
	 * @var array<\F3\FLOW3\I18n\Locale>
	 */
	protected $localeCollection = array();

	/**
	 * This array contains a parent Locale objects for given locale.
	 * 
	 * "Searching" is done by the keys, which are locale tags. The key points to
	 * the value which is a parent Locale object. If it's not set, there is no
	 * parent for given locale, or no parent was searched before.
	 *
	 * @var array<\F3\FLOW3\I18n\Locale>
	 */
	protected $localeParentCollection = array();

	/**
	 * Adds a locale to the collection.
	 *
	 * @param \F3\FLOW3\I18n\Locale $locale The Locale to be inserted
	 * @return boolean FALSE when same locale was already inserted before
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function addLocale(\F3\FLOW3\I18n\Locale $locale) {
		if (isset($this->localeCollection[(string)$locale])) {
			return FALSE;
		}

			// We need to invalidate the parent's array as it could be inaccurate
		$this->localeParentCollection = array();

		$this->localeCollection[(string)$locale] = $locale;
		return TRUE;
	}

	/**
	 * Returns a parent Locale object of the locale provided.
	 * 
	 * The parent is a locale which is more generic than the one given as
	 * parameter. For example, the parent for locale en_GB will be locale en, of
	 * course if it exists in the locale tree of available locales.
	 *
	 * This method returns NULL when no parent locale is available, or when
	 * Locale object provided is not in three (ie it's not in a group of
	 * available locales).
	 *
	 * Note: to find a best-matching locale to one which doesn't exist in the
	 * system, please use findBestMatchingLocale() method of this class.
	 *
	 * @param \F3\FLOW3\I18n\Locale $locale The Locale to search parent for
	 * @return mixed Existing \F3\FLOW3\I18n\Locale instance or NULL on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function getParentLocaleOf(\F3\FLOW3\I18n\Locale $locale) {
		$localeIdentifier = (string)$locale;

		if (!isset($this->localeCollection[$localeIdentifier])) {
			return NULL;
		}

		if (isset($this->localeParentCollection[$localeIdentifier])) {
			return $this->localeParentCollection[$localeIdentifier];
		}

		$parentLocaleIdentifier = $localeIdentifier;
		do {
				// Remove the last (most specific) part of the locale tag
			$parentLocaleIdentifier = substr($parentLocaleIdentifier, 0, (int)strrpos($parentLocaleIdentifier, '_'));

			if (isset($this->localeCollection[$parentLocaleIdentifier])) {
				return $this->localeParentCollection[$localeIdentifier] = $this->localeCollection[$parentLocaleIdentifier];
			}
		} while (strrpos($parentLocaleIdentifier, '_') !== FALSE);

		return NULL;
	}

	/**
	 * Returns Locale object which represents one of locales installed and which
	 * is most similar to the "template" Locale object given as parameter.
	 *
	 * @param \F3\FLOW3\I18n\Locale $locale The "template" locale to be matched
	 * @return mixed Existing \F3\FLOW3\I18n\Locale instance on success, NULL on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function findBestMatchingLocale(\F3\FLOW3\I18n\Locale $locale) {
		$localeIdentifier = (string)$locale;

		if (isset($this->localeCollection[$localeIdentifier])) {
			return $this->localeCollection[$localeIdentifier];
		}

		$parentLocaleIdentifier = $localeIdentifier;
		do {
				// Remove the last (most specific) part of the locale tag
			$parentLocaleIdentifier = substr($parentLocaleIdentifier, 0, (int)strrpos($parentLocaleIdentifier, '_'));

			if (isset($this->localeCollection[$parentLocaleIdentifier])) {
				return $this->localeCollection[$parentLocaleIdentifier];
			}
		} while (strrpos($parentLocaleIdentifier, '_') !== FALSE);

		return NULL;
	}
}

?>