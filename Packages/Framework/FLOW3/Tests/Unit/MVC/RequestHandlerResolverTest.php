<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\MVC;

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
 * Testcase for the MVC Request Handler Resolver
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RequestHandlerResolverTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function theRequestHandlersAreFoundByTheirInterfaceAndAreThenChosenByTheirFitAndPriority() {
		$mockRequestHandler1 = $this->getMock('F3\FLOW3\MVC\RequestHandlerInterface');
		$mockRequestHandler1->expects($this->once())->method('canHandleRequest')->will($this->returnValue(FALSE));

		$mockRequestHandler2 = $this->getMock('F3\FLOW3\MVC\RequestHandlerInterface');
		$mockRequestHandler2->expects($this->once())->method('canHandleRequest')->will($this->returnValue(TRUE));
		$mockRequestHandler2->expects($this->once())->method('getPriority')->will($this->returnValue(100));

		$mockRequestHandler3 = $this->getMock('F3\FLOW3\MVC\RequestHandlerInterface');
		$mockRequestHandler3->expects($this->once())->method('canHandleRequest')->will($this->returnValue(-1));

		$mockRequestHandler4 = $this->getMock('F3\FLOW3\MVC\RequestHandlerInterface');
		$mockRequestHandler4->expects($this->once())->method('canHandleRequest')->will($this->returnValue(1));
		$mockRequestHandler4->expects($this->once())->method('getPriority')->will($this->returnValue(200));

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->at(0))->method('isRegistered')->with('RequestHandler1')->will($this->returnValue(TRUE));
		$mockObjectManager->expects($this->at(1))->method('get')->with('RequestHandler1')->will($this->returnValue($mockRequestHandler1));
		$mockObjectManager->expects($this->at(2))->method('isRegistered')->with('RequestHandler2')->will($this->returnValue(TRUE));
		$mockObjectManager->expects($this->at(3))->method('get')->with('RequestHandler2')->will($this->returnValue($mockRequestHandler2));
		$mockObjectManager->expects($this->at(4))->method('isRegistered')->with('RequestHandler3')->will($this->returnValue(TRUE));
		$mockObjectManager->expects($this->at(5))->method('get')->with('RequestHandler3')->will($this->returnValue($mockRequestHandler3));
		$mockObjectManager->expects($this->at(6))->method('isRegistered')->with('RequestHandler4')->will($this->returnValue(TRUE));
		$mockObjectManager->expects($this->at(7))->method('get')->with('RequestHandler4')->will($this->returnValue($mockRequestHandler4));

		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->once())
			->method('getAllImplementationClassNamesForInterface')
			->with('F3\FLOW3\MVC\RequestHandlerInterface')
			->will($this->returnValue(array('RequestHandler1', 'RequestHandler2', 'RequestHandler3', 'RequestHandler4')));

		$resolver = new \F3\FLOW3\MVC\RequestHandlerResolver();
		$resolver->injectObjectManager($mockObjectManager);
		$resolver->injectReflectionService($mockReflectionService);

		$this->assertSame($mockRequestHandler4, $resolver->resolveRequestHandler());
	}
}

?>