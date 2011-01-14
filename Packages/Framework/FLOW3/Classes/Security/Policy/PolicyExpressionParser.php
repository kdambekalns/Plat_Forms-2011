<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\Policy;

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
 * A specialized pointcut expression parser tailored to policy expressions
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PolicyExpressionParser extends \F3\FLOW3\AOP\Pointcut\PointcutExpressionParser {

	/**
	 * @var array The resources array from the configuration.
	 */
	protected $methodResourcesTree = array();

	/**
	 * Performs a circular reference detection and calls the (parent) parse function afterwards
	 *
	 * @param string $pointcutExpression The pointcut expression to parse
	 * @param array $methodResourcesTree The method resources tree
	 * @param array $trace A trace of all visited pointcut expression, used for circular reference detection
	 * @return \F3\FLOW3\AOP\Pointcut\PointcutFilterComposite A composite of class-filters, method-filters and pointcuts
	 * @throws \F3\FLOW3\Security\Exception\CircularResourceDefinitionDetectedException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function parseMethodResources($pointcutExpression, array $methodResourcesTree, array &$trace = array()) {
		if (!is_string($pointcutExpression) || strlen($pointcutExpression) === 0) throw new \F3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Pointcut expression must be a valid string, ' . gettype($pointcutExpression) . ' given.', 1168874738);
		if (count($methodResourcesTree) > 0) $this->methodResourcesTree = $methodResourcesTree;

		$pointcutFilterComposite = $this->objectManager->create('F3\FLOW3\AOP\Pointcut\PointcutFilterComposite');
		$pointcutExpressionParts = preg_split(parent::PATTERN_SPLITBYOPERATOR, $pointcutExpression, -1, PREG_SPLIT_DELIM_CAPTURE);

		for ($partIndex = 0; $partIndex < count($pointcutExpressionParts); $partIndex += 2) {
			$operator = ($partIndex > 0) ? trim($pointcutExpressionParts[$partIndex - 1]) : '&&';
			$expression = trim($pointcutExpressionParts[$partIndex]);

			if ($expression[0] === '!') {
				$expression = trim(substr($expression, 1));
				$operator .= '!';
			}

			if (strpos($expression, '(') === FALSE) {
				if (in_array($expression, $trace)) throw new \F3\FLOW3\Security\Exception\CircularResourceDefinitionDetectedException('A circular reference was detected in the security policy resources definition. Look near: ' . $expression, 1222028842);
				$trace[] = $expression;
				$this->parseDesignatorPointcut($operator, $expression, $pointcutFilterComposite, $trace);
			}
		}

		return $this->parse($pointcutExpression);
	}

	/**
	 * Parses the security constraints configured for persistence entities
	 *
	 * @param array $entityResourcesTree The tree of all available entity resources
	 * @return array The constraints definition array for all entity resources
	 * @throws \F3\FLOW3\Security\Exception\CircularResourceDefinitionDetectedException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function parseEntityResources(array $entityResourcesTree) {
		$entityResourcesConstraints = array();

		foreach ($entityResourcesTree as $entityType => $entityResources) {
			foreach ($entityResources as $resourceName => $constraintDefinition) {
				$entityResourcesConstraints[$entityType][$resourceName] = $this->parseSingleEntityResource($resourceName, $entityResources);
			}
		}

		return $entityResourcesConstraints;
	}

	/**
	 * Walks recursively through the method resources tree.
	 *
	 * @param string $operator The operator
	 * @param string $pointcutExpression The pointcut expression (value of the designator)
	 * @param \F3\FLOW3\AOP\Pointcut\PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the pointcut filter) will be added to this composite object.
	 * @return void
	 * @throws \F3\FLOW3\AOP\Exception\InvalidPointcutExpressionException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function parseDesignatorPointcut($operator, $pointcutExpression, \F3\FLOW3\AOP\Pointcut\PointcutFilterComposite $pointcutFilterComposite, array &$trace = array()) {
		if (!isset($this->methodResourcesTree[$pointcutExpression])) throw new \F3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('The given resource was not defined: ' . $pointcutExpression . '".', 1222014591);

		$pointcutFilterComposite->addFilter($operator, $this->parseMethodResources($this->methodResourcesTree[$pointcutExpression], array(), $trace));
	}

	/**
	 * Parses the security constraints configured for a single entity resource. If needed
	 * it walks recursively through the entity resources tree array.
	 *
	 * @param string $resourceName The name of the resource to be parsed
	 * @param array $entityResourcesTree The tree of all available resources for one entity
	 * @return array The constraints definition array
	 * @throws \F3\FLOW3\Security\Exception\CircularResourceDefinitionDetectedException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function parseSingleEntityResource($resourceName, array $entityResourcesTree) {
		$expressionParts = preg_split(parent::PATTERN_SPLITBYOPERATOR, $entityResourcesTree[$resourceName], -1, PREG_SPLIT_DELIM_CAPTURE);

		$constraints = array();
		for ($i = 0; $i < count($expressionParts); $i += 2) {
			$operator = ($i > 1 ? $expressionParts[$i - 1] : '&&');

			if (!isset($constraints[$operator])) $constraints[$operator] = array();

			if (preg_match('/\s(==|!=|<=|>=|<|>|in|contains|matches)\s/', $expressionParts[$i]) > 0) {
				$constraints[$operator] = array_merge($constraints[$operator], $this->getRuntimeEvaluationConditionsFromEvaluateString($expressionParts[$i]));
			} else {
				if (!isset($entityResourcesTree[$expressionParts[$i]])) throw new \F3\FLOW3\Security\Exception\NoEntryInPolicyException('Entity resource "' . $expressionParts[$i] . '" not found in policy.', 1267722067);
				$constraints[$operator]['subConstraints'] = $this->parseSingleEntityResource($expressionParts[$i], $entityResourcesTree);
			}
		}

		return $constraints;
	}
}
?>