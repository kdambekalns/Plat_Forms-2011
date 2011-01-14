<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\AOP\Builder;

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
 * Testcase for the AOP Empty Method Interceptor Builder
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class EmptyMethodInterceptorBuilderTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildRendersCodeOfAPlaceHolderMethod() {
		$className = uniqid('TestClass');
		eval('
			class ' . $className . ' {
				static public function foo($arg1, array $arg2, \ArrayObject $arg3, $arg4= "foo", $arg5 = TRUE) {}
			}
		');

		$interceptedMethods = array(
			'foo' => array(
				'groupedAdvices' => array('groupedAdvicesDummy'),
				'declaringClassName' => $className
			)
		);

		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\ReflectionService', array('loadFromCache', 'saveToCache'), array(), '', FALSE, TRUE);
		$mockReflectionService->initialize(array($className));

		$expectedCode = '
	/**
	 * Placeholder for the method foo() declared in
	 * ' . $className . '.
	 * ' . '
	 * @return void
	 */
	static public function foo(PARAMETERSCODE1) {}
';

		$builder = $this->getMock('F3\FLOW3\AOP\Builder\EmptyMethodInterceptorBuilder', array('buildMethodParametersCode'), array(), '', FALSE);
		$builder->injectReflectionService($mockReflectionService);
		$builder->expects($this->at(0))->method('buildMethodParametersCode')->with($className, 'foo', TRUE)->will($this->returnValue('PARAMETERSCODE1'));

		$actualCode = $builder->build('foo', $interceptedMethods, 'Bar');
		$this->assertSame($expectedCode, $actualCode);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildRendersWakeupCodeIfTheWakeupMethodShouldBeIntercepted() {
		$className = uniqid('TestClass');
		eval('
			class ' . $className . ' {
				static public function __wakeup() {}
			}
		');

		$interceptedMethods = array(
			'__wakeup' => array(
				'groupedAdvices' => array('groupedAdvicesDummy'),
				'declaringClassName' => $className
			)
		);

		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\ReflectionService', array('loadFromCache', 'saveToCache'), array(), '', FALSE, TRUE);
		$mockReflectionService->initialize(array($className));

		$expectedCode = '
	/**
	 * Placeholder for the method __wakeup() declared in
	 * ' . $className . '.
	 * ' . '
	 * @return void
	 */
	static public function __wakeup(PARAMETERSCODE1) {}
';

		$builder = $this->getMock('F3\FLOW3\AOP\Builder\EmptyMethodInterceptorBuilder', array('buildMethodParametersCode', 'buildWakeupCode'), array(), '', FALSE);
		$builder->injectReflectionService($mockReflectionService);
		$builder->expects($this->at(0))->method('buildMethodParametersCode')->with($className, '__wakeup', TRUE)->will($this->returnValue('PARAMETERSCODE1'));

		$actualCode = $builder->build('__wakeup', $interceptedMethods, 'Bar');
		$this->assertSame($expectedCode, $actualCode);
	}
}
?>