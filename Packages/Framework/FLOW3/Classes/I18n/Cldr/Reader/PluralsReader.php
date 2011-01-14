<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n\Cldr\Reader;

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
 * A reader for data placed in "plurals" tag in CLDR.
 *
 * There are a few similar words used in plurals.xml file of CLDR used by this
 * class. Following naming convention is used in the code (a name of
 * corresponding tag from xml file is provided in brackets, if any):
 * - ruleset: a set of plural rules for a locale [pluralRules]
 * - rule: a rule for one of the forms: zero, one, two, few, many [pluralRule]
 * - subrule: one of the conditions of rule. One rule can have many conditions
 *   joined with "and" or "or" logical operator.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @see http://www.unicode.org/reports/tr35/#Language_Plural_Rules
 */
class PluralsReader {

	/**
	 * An expression to catch one plural subrule. One rule consists of one or
	 * more subrules.
	 *
	 * @todo improve the regexp pattern
	 */
	const PATTERN_MATCH_SUBRULE = '/(n|nmod)([0-9]+)?(is|isnot|in|notin|within|notwithin)([0-9]+)(?:\.\.([0-9]+))?(and|or)?/';

	/**
	 * Constants for every plural rule form defined in CLDR.
	 */
	const RULE_ZERO = 'zero';
	const RULE_ONE = 'one';
	const RULE_TWO = 'two';
	const RULE_FEW = 'few';
	const RULE_MANY = 'many';
	const RULE_OTHER = 'other';

	/**
	 * @var \F3\FLOW3\I18n\Cldr\CldrRepository
	 */
	protected $cldrRepository;

	/**
	 * @var \F3\FLOW3\Cache\Frontend\VariableFrontend
	 */
	protected $cache;

	/**
	 * An array of rulesets, indexed numerically.
	 *
	 * One ruleset contains one or more rules (at most 5, one for every plural
	 * form - zero, one, two, few, many - a rule 'other' is implicit). There can
	 * also be NULL ruleset, used by languages which don't have plurals.
	 *
	 * A rule is an array with following elements:
	 * 'modulo' => $x | FALSE,
	 * 'condition' => array(0 => 'conditionName', 1 => $x, 2 => $y),
	 * 'logicalOperator' => 'and' | 'or' | FALSE
	 *
	 * Legend:
	 * - if 'modulo' key has an integer value, tested variable (call it $n) has
	 *   to be replaced with the remainder of division of $n by $x. Otherwise
	 *   unchanged $n is used for conditional test.
	 * - 'condition' is an indexed array where first element is a name of test
	 *   condition (one of: is, isnot, in, notin, within, notwithin). Second
	 *   element is a value to compare $n with. Third element is optional, and
	 *   is used only for tests where range is needed (last 4 from the list above)
	 * - 'logicalOperator' represents a logical operation to be done with next
	 *   subrule in chain. If current subrule is a last one (or only one), this
	 *   element is set to FALSE.
	 *
	 * @var array
	 */
	protected $rulesets;

	/**
	 * An assocciative array holding information which ruleset is used by given
	 * locale. One or more locales can use the same ruleset.
	 *
	 * @var array
	 */
	protected $rulesetsIndices;

	/**
	 * @param \F3\FLOW3\I18n\Cldr\CldrRepository $repository
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectCldrRepository(\F3\FLOW3\I18n\Cldr\CldrRepository $repository) {
		$this->cldrRepository = $repository;
	}

	/**
	 * Injects the FLOW3_I18n_Cldr_Reader_PluralsReader cache
	 *
	 * @param \F3\FLOW3\Cache\Frontend\VariableFrontend $cache
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function injectCache(\F3\FLOW3\Cache\Frontend\VariableFrontend $cache) {
		$this->cache = $cache;
	}

	/**
	 * Constructs the reader, loading parsed data from cache if available.
	 *
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function initializeObject() {
		if ($this->cache->has('rulesets') && $this->cache->has('rulesetsIndices')) {
			$this->rulesets = $this->cache->get('rulesets');
			$this->rulesetsIndices = $this->cache->get('rulesetsIndices');
		} else {
			$this->generateRulesets();
			$this->cache->set('rulesets', $this->rulesets);
			$this->cache->set('rulesetsIndices', $this->rulesetsIndices);
		}
	}

	/**
	 * Returns matching plural form based on $quantity and $locale provided.
	 *
	 * Plural form is one of following: zero, one, two, few, many, other.
	 * Last one (other) is returned when number provided doesn't match any
	 * of the rules, or there is no rules for given locale.
	 *
	 * @param mixed $quantity A number to find plural form for (float or int)
	 * @param \F3\FLOW3\I18n\Locale $locale
	 * @return string One of plural form constants
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function getPluralForm($quantity, \F3\FLOW3\I18n\Locale $locale) {
		if (!isset($this->rulesetsIndices[$locale->getLanguage()])) {
			return self::RULE_OTHER;
		}

		$ruleset = $this->rulesets[$this->rulesetsIndices[$locale->getLanguage()]];

		if ($ruleset === NULL) {
			return self::RULE_OTHER;
		}

		foreach ($ruleset as $form => $rule) {
			foreach ($rule as $subrule) {
				$subrulePassed = FALSE;

				if ($subrule['modulo'] !== FALSE) {
					$quantity = fmod($quantity, $subrule['modulo']);
				}

				if ($quantity == floor($quantity)) {
					$quantity = (int)$quantity;
				}

				$condition = $subrule['condition'];
				switch ($condition[0]) {
					case 'is':
					case 'isnot':
						if (is_int($quantity) && $quantity === $condition[1]) $subrulePassed = TRUE;
						if ($condition[0] === 'isnot') $subrulePassed = !$subrulePassed;
						break;
					case 'in':
					case 'notin':
						if (is_int($quantity) && $quantity >= $condition[1] && $quantity <= $condition[2]) $subrulePassed = TRUE;
						if ($condition[0] === 'notin') $subrulePassed = !$subrulePassed;
						break;
					case 'within':
					case 'notwithin':
						if ($quantity >= $condition[1] && $quantity <= $condition[2]) $subrulePassed = TRUE;
						if ($condition[0] === 'notwithin') $subrulePassed = !$subrulePassed;
						break;
				}

				if (($subrulePassed && $subrule['logicalOperator'] === 'or') || (!$subrulePassed && $subrule['logicalOperator'] === 'and')) {
					break;
				}
			}

			if ($subrulePassed) {
				return $form;
			}
		}

		return self::RULE_OTHER;
	}

	/**
	 * Returns array of plural forms available for particular locale.
	 *
	 * @param \F3\FLOW3\I18n\Locale $locale Locale to return plural forms for
	 * @return array Plural forms' names (one, zero, two, few, many, other) available for language set in this model
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function getPluralForms(\F3\FLOW3\I18n\Locale $locale) {
		if (!isset($this->rulesetsIndices[$locale->getLanguage()])) {
			return array(self::RULE_OTHER);
		}

		return array_merge(array_keys($this->rulesets[$this->rulesetsIndices[$locale->getLanguage()]]), array(self::RULE_OTHER));
	}

	/**
	 * Generates an internal representation of plural rules which can be found
	 * in plurals.xml CLDR file.
	 *
	 * The properties $rulesets and $rulesetsIndices should be empty before
	 * running this method.
	 *
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 * @see \F3\FLOW3\I18n\Cldr\Reader\PluralsReader::$rulesets
	 */
	protected function generateRulesets() {
		$model = $this->cldrRepository->getModel('supplemental/plurals');
		$pluralRulesSet = $model->getRawArray('plurals');

		$index = 0;
		foreach ($pluralRulesSet as $pluralRulesNodeString => $pluralRules) {
			$localeLanguages = $model->getAttributeValue($pluralRulesNodeString, 'locales');

			foreach (explode(' ', $localeLanguages) as $localeLanguage) {
				$this->rulesetsIndices[$localeLanguage] = $index;
			}

			if (!is_array($pluralRules)) {
				$this->rulesets[$index] = NULL;
				continue;
			}

			$ruleset = array();
			foreach ($pluralRules as $pluralRuleNodeString => $pluralRule) {
				$pluralForm = $model->getAttributeValue($pluralRuleNodeString, 'count');
				$ruleset[$pluralForm] = $this->parseRule($pluralRule);
			}

			$this->rulesets[$index] = $ruleset;

			++$index;
		}
	}

	/**
	 * Parses a plural rule from CLDR.
	 *
	 * A plural rule in CLDR is a string with one or more test conditions, with
	 * 'and' or 'or' logical operators between them. Whole expression can look
	 * like this:
	 *
	 * n is 0 OR n is not 1 AND n mod 100 in 1..19
	 *
	 * As CLDR documentation says, following test conditions can be used:
	 * - is x, is not x: $n is (not) equal $x
	 * - in x..y, not in x..y: $n is (not) one of integers from range <$x, $y>
	 * - within x..y, not within x..y: $n is (not) any number from range <$x, $y>
	 *
	 * Where $n can be a number (also float) as is, or a result of $n mod $x.
	 *
	 * Array returned follows simple internal format (see documentation for
	 * $rulesets property for details).
	 *
	 * @param string $rule
	 * @return array Parsed rule
	 * @throws \F3\FLOW3\I18n\Cldr\Reader\Exception\InvalidPluralRuleException When plural rule does not match regexp pattern
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	protected function parseRule($rule) {
		$parsedRule = array();

		if (preg_match_all(self::PATTERN_MATCH_SUBRULE, strtolower(str_replace(' ', '', $rule)), $matches, \PREG_SET_ORDER)) {
			foreach ($matches as $matchedSubrule) {
				$subrule = array();

				if ($matchedSubrule[1] === 'nmod') {
					$subrule['modulo'] = (int)$matchedSubrule[2];
				} else {
					$subrule['modulo'] = FALSE;
				}

				$condition = array($matchedSubrule[3], (int)$matchedSubrule[4]);
				if (!in_array($matchedSubrule[3], array('is', 'isnot'), TRUE)) {
					$condition[2] = (int)$matchedSubrule[5];
				}

				$subrule['condition'] = $condition;

				if (isset($matchedSubrule[6]) && ($matchedSubrule[6] === 'and' || $matchedSubrule[6] === 'or')) {
					$subrule['logicalOperator'] = $matchedSubrule[6];
				} else {
					$subrule['logicalOperator'] = FALSE;
				}

				$parsedRule[] = $subrule;
			}
		} else {
			throw new \F3\FLOW3\I18n\Cldr\Reader\Exception\InvalidPluralRuleException('A plural rule string is invalid. CLDR files might be corrupted.', 1275493982);
		}

		return $parsedRule;
	}
}

?>