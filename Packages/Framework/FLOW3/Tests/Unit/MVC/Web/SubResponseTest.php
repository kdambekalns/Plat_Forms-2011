<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\MVC\Web;

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
 * Testcase for the MVC Web SubResponse class
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class SubResponseTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function constructorSetsParentResponse() {
		$mockResponse = $this->getMock('F3\FLOW3\MVC\Web\Response');
		$subResponse = new \F3\FLOW3\MVC\Web\SubResponse($mockResponse);
		$this->assertSame($mockResponse, $subResponse->getParentResponse());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setStatusSetsStatusOfParentResponse() {
		$mockResponse = $this->getMock('F3\FLOW3\MVC\Web\Response');
		$mockResponse->expects($this->once())->method('setStatus')->with('SomeStatusCode', 'SomeStatusMessage');
		$subResponse = new \F3\FLOW3\MVC\Web\SubResponse($mockResponse);
		$subResponse->setStatus('SomeStatusCode', 'SomeStatusMessage');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setHeaderSetsHeaderOfParentResponse() {
		$mockResponse = $this->getMock('F3\FLOW3\MVC\Web\Response');
		$mockResponse->expects($this->once())->method('setHeader')->with('SomeName', 'SomeValue', FALSE);
		$subResponse = new \F3\FLOW3\MVC\Web\SubResponse($mockResponse);
		$subResponse->setHeader('SomeName', 'SomeValue', FALSE);
	}
}
?>