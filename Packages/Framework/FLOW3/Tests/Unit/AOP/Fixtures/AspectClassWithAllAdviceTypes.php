<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\AOP\Fixture;

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
 * An aspect class which contains all supported types of advice, a pointcut and an introduction
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @aspect
 */
class AspectClassWithAllAdviceTypes {

	/**
	 * @introduce F3\FLOW3\Tests\AOP\Fixture\InterfaceForIntroduction, ThePointcutExpression
	 */
	protected $introduction;

	/**
	 * @around fooAround
	 */
	public function aroundAdvice() {}

	/**
	 * @before fooBefore
	 */
	public function beforeAdvice() {}

	/**
	 * @afterreturning fooAfterReturning
	 */
	public function afterReturningAdvice() {}

	/**
	 * @afterthrowing fooAfterThrowing
	 */
	public function afterThrowingAdvice() {}

	/**
	 * @after fooAfter
	 */
	public function afterAdvice() {}

	/**
	 * @pointcut fooPointcut
	 */
	public function pointcut() {}

}
?>