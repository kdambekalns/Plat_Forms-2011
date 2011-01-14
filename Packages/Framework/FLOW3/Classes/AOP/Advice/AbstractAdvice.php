<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\AOP\Advice;

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
 * Base class for Advices.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class AbstractAdvice implements \F3\FLOW3\AOP\Advice\AdviceInterface {

	/**
	 * @var string Holds the name of the aspect object containing the advice
	 */
	protected $aspectObjectName;

	/**
	 * @var string Contains the name of the advice method
	 */
	protected $adviceMethodName;

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface A reference to the Object Manager
	 */
	protected $objectManager;

	/**
	 * @var array Runtime evaluations definition array
	 */
	protected $runtimeEvaluationsDefinition;

	/**
	 * @var \Closure Runtime evaluations function
	 */
	protected $runtimeEvaluator;

	/**
	 * Constructor
	 *
	 * @param string $aspectObjectName Name of the aspect object containing the advice
	 * @param string $adviceMethodName Name of the advice method
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager A reference to the object manager
	 * @param \Closure $runtimeEvaluator Runtime evaluations function
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct($aspectObjectName, $adviceMethodName, \F3\FLOW3\Object\ObjectManagerInterface $objectManager, \Closure $runtimeEvaluator = NULL) {
		$this->aspectObjectName = $aspectObjectName;
		$this->adviceMethodName = $adviceMethodName;
		$this->objectManager = $objectManager;
		$this->runtimeEvaluator = $runtimeEvaluator;
	}

	/**
	 * Invokes the advice method
	 *
	 * @param \F3\FLOW3\AOP\JoinPointInterface $joinPoint The current join point which is passed to the advice method
	 * @return Result of the advice method
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function invoke(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		if ($this->runtimeEvaluator !== NULL && $this->runtimeEvaluator->__invoke($joinPoint) === FALSE) return;

		$adviceObject = $this->objectManager->get($this->aspectObjectName);
		$methodName = $this->adviceMethodName;
		$adviceObject->$methodName($joinPoint);
	}

	/**
	 * Returns the aspect's object name which has been passed to the constructor
	 *
	 * @return string The object name of the aspect
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getAspectObjectName() {
		return $this->aspectObjectName;
	}

	/**
	 * Returns the advice's method name which has been passed to the constructor
	 *
	 * @return string The name of the advice method
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getAdviceMethodName() {
		return $this->adviceMethodName;
	}
}

?>