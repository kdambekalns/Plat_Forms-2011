<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n\Xliff;

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
 * A model representing data from one XLIFF file.
 *
 * Please note that plural forms for particular translation unit are accessed
 * with integer index (and not string like 'zero', 'one', 'many' etc). This is
 * because they are indexed such way in XLIFF files in order to not break tools'
 * support.
 *
 * There are very few XLIFF editors, but they are nice Gettext's .po editors
 * available. Gettext supports plural forms, but it indexes them using integer
 * numbers. Leaving it this way in .xlf files, makes possible to easly convert
 * them to .po (e.g. using xliff2po from Translation Toolkit), edit with Poedit,
 * and convert back to .xlf without any information loss (using po2xliff).
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @see http://docs.oasis-open.org/xliff/v1.2/xliff-profile-po/xliff-profile-po-1.2-cd02.html#s.detailed_mapping.tu
 * @scope prototype
 */
class XliffModel extends \F3\FLOW3\I18n\Xml\AbstractXmlModel {

	/**
	 * @param \F3\FLOW3\I18n\Xliff\XliffParser $parser
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectParser(\F3\FLOW3\I18n\Xliff\XliffParser $parser) {
		$this->xmlParser = $parser;
	}

	/**
	 * Returns translated label ("target" tag in XLIFF) from source-target
	 * pair where "source" tag equals to $source parameter.
	 *
	 * @param string $source Label in original language ("source" tag in XLIFF)
	 * @param string $pluralFormIndex Index of plural form to use (starts with 0)
	 * @return mixed Translated label or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function getTargetBySource($source, $pluralFormIndex = 0) {
		foreach ($this->xmlParsedData as $translationUnit) {
				// $source is always singular (or only) form, so compare with index 0
			if ($translationUnit[0]['source'] !== $source) {
				continue;
			}

			if (count($translationUnit) <= $pluralFormIndex) {
				return FALSE;
			}

			return $translationUnit[$pluralFormIndex]['target'];
		}

		return FALSE;
	}

	/**
	 * Returns translated label ("target" tag in XLIFF) for the id given.
	 *
	 * Id is compared with "id" attribute of "trans-unit" tag (see XLIFF
	 * specification for details).
	 *
	 * @param string $transUnitId The "id" attribute of "trans-unit" tag in XLIFF
	 * @param string $pluralFormIndex Index of plural form to use (starts with 0)
	 * @return mixed Translated label or FALSE on failure
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function getTargetByTransUnitId($transUnitId, $pluralFormIndex = 0) {
		if (!isset($this->xmlParsedData[$transUnitId])) {
			return FALSE;
		}

		$translationUnit = $this->xmlParsedData[$transUnitId];
		if (count($translationUnit) <= $pluralFormIndex) {
			return FALSE;
		}

		return $translationUnit[$pluralFormIndex]['target'];
	}
}

?>