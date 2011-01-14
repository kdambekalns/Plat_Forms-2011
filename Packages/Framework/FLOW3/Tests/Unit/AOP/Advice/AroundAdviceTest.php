<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\AOP\Advice;

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
 * Testcase for the Abstract Method Interceptor Builder
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AroundAdviceTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function invokeInvokesTheAdviceIfTheRuntimeEvaluatorReturnsTrue() {
		$mockJoinPoint = $this->getMock('F3\FLOW3\AOP\JoinPointInterface', array(), array(), '', FALSE);

		$mockAspect = $this->getMock(uniqid('MockClass'), array('someMethod'));
		$mockAspect->expects($this->once())->method('someMethod')->with($mockJoinPoint)->will($this->returnValue('result'));

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->once())->method('get')->with('aspectObjectName')->will($this->returnValue($mockAspect));

		$advice = new \F3\FLOW3\AOP\Advice\AroundAdvice('aspectObjectName', 'someMethod', $mockObjectManager, function(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) { if ($joinPoint !== NULL) return TRUE; });
		$result = $advice->invoke($mockJoinPoint);

		$this->assertEquals($result, 'result', 'The around advice did not return the result value as expected.');
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function invokeDoesNotInvokeTheAdviceIfTheRuntimeEvaluatorReturnsFalse() {
		$mockAdviceChain = $this->getMock('F3\FLOW3\AOP\Advice\AdviceChain', array(), array(), '', FALSE);
		$mockAdviceChain->expects($this->once())->method('proceed')->will($this->returnValue('result'));

		$mockJoinPoint = $this->getMock('F3\FLOW3\AOP\JoinPointInterface', array(), array(), '', FALSE);
		$mockJoinPoint->expects($this->any())->method('getAdviceChain')->will($this->returnValue($mockAdviceChain));

		$mockAspect = $this->getMock(uniqid('MockClass'), array('someMethod'));
		$mockAspect->expects($this->never())->method('someMethod');

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->any())->method('get')->will($this->returnValue($mockAspect));

		$advice = new \F3\FLOW3\AOP\Advice\AroundAdvice('aspectObjectName', 'someMethod', $mockObjectManager, function(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) { if ($joinPoint !== NULL) return FALSE; });
		$result = $advice->invoke($mockJoinPoint);

		$this->assertEquals($result, 'result', 'The around advice did not return the result value as expected.');
	}
}
?>