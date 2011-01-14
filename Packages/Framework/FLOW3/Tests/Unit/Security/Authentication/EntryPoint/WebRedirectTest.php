<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Security\Authentication\EntryPoint;

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
 * Testcase for web redirect authentication entry point
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class WebRedirectTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function canForwardReturnsTrueForWebRequests() {
		$entryPoint = new \F3\FLOW3\Security\Authentication\EntryPoint\WebRedirect();

		$this->assertTrue($entryPoint->canForward($this->getMock('F3\FLOW3\MVC\Web\Request')));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function canForwardReturnsFalseForNonWebRequests() {
		$entryPoint = new \F3\FLOW3\Security\Authentication\EntryPoint\WebRedirect();

		$this->assertFalse($entryPoint->canForward($this->getMock('F3\FLOW3\MVC\CLI\Request')));
		$this->assertFalse($entryPoint->canForward($this->getMock('F3\FLOW3\MVC\RequestInterface')));
	}

	/**
	 * @test
	 * @category unit
	 * @expectedException F3\FLOW3\Security\Exception\MissingConfigurationException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function startAuthenticationThrowsAnExceptionIfTheConfigurationOptionsAreMissing() {
		$entryPoint = new \F3\FLOW3\Security\Authentication\EntryPoint\WebRedirect();
		$entryPoint->setOptions(array('something' => 'irrelevant'));

		$entryPoint->startAuthentication($this->getMock('F3\FLOW3\MVC\Web\Request'), $this->getMock('F3\FLOW3\MVC\Web\Response'));
	}

	/**
	 * @test
	 * @category unit
	 * @expectedException F3\FLOW3\Security\Exception\RequestTypeNotSupportedException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function startAuthenticationThrowsAnExceptionIfItsCalledWithAnUnsupportedRequestType() {
		$entryPoint = new \F3\FLOW3\Security\Authentication\EntryPoint\WebRedirect();

		$entryPoint->startAuthentication($this->getMock('F3\FLOW3\MVC\CLI\Request'), $this->getMock('F3\FLOW3\MVC\CLI\Response'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function startAuthenticationSetsTheCorrectValuesInTheResponseObject() {
		$request = $this->getMock('F3\FLOW3\MVC\Web\Request');
		$response = $this->getMock('F3\FLOW3\MVC\Web\Response');

		$response->expects($this->once())->method('setStatus')->with(303);
		$response->expects($this->once())->method('setHeader')->with('Location', 'some/page');

		$entryPoint = new \F3\FLOW3\Security\Authentication\EntryPoint\WebRedirect();
		$entryPoint->setOptions(array('uri' => 'some/page'));

		$entryPoint->startAuthentication($request, $response);
	}
}
?>