<?php
declare(ENCODING = 'utf-8');
namespace F3\ExtJS\Tests\Unit\ExtDirect;

/*                                                                        *
 * This script belongs to the FLOW3 package "ExtJS".                      *
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
 * Testcase for the ExtDirect Request Handler
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RequestHandlerTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function canHandleRequestReturnsTrueIfTheSapiTypeIsWebAndAnExtDirectGetParameterIsSent() {
		$mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment');
		$mockEnvironment->expects($this->at(0))->method('getRawGetArguments')->will($this->returnValue(array('foo' => 'bar', 'baz' => 'quux')));
		$mockEnvironment->expects($this->at(1))->method('getRawGetArguments')->will($this->returnValue(array('foo' => 'bar', 'F3_ExtJS_ExtDirectRequest' => '1')));

		$requestHandler = $this->getAccessibleMock('F3\ExtJS\ExtDirect\RequestHandler', array('dummy'), array(), '', FALSE);
		$requestHandler->_set('environment', $mockEnvironment);

		$this->assertFalse($requestHandler->canHandleRequest());
		$this->assertTrue($requestHandler->canHandleRequest());
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function handleRequestCatchesAndLogsExceptionsAndReturnsThemInTheTransaction() {
		$mockSystemLogger = $this->getMock('F3\FLOW3\Log\SystemLoggerInterface');

		$mockRequest = $this->getMock('F3\ExtJS\ExtDirect\Request', array('getTransactions'));

		$mockTransactionRequest = $this->getMock('F3\FLOW3\MVC\Web\Request');

		$mockTransaction = $this->getMock('F3\ExtJS\ExtDirect\Transaction', array('buildRequest', 'buildResponse'), array($mockRequest, 'someAction', 'someMethod', array(), 42));
		$mockTransaction->expects($this->any())->method('buildRequest')->will($this->returnValue($mockTransactionRequest));

		$mockRequest->expects($this->any())->method('getTransactions')->will($this->returnValue(array($mockTransaction)));

		$mockRequestBuilder = $this->getMock('F3\ExtJS\ExtDirect\RequestBuilder', array('build'));
		$mockRequestBuilder->expects($this->any())->method('build')->will($this->returnValue($mockRequest));

		$mockException = $this->getMock('Exception');

		$mockDispatcher = $this->getMock('F3\FLOW3\MVC\Dispatcher', array('dispatch'), array(), '', FALSE);
		$mockDispatcher->expects($this->any())->method('dispatch')->will($this->throwException($mockException));

		$mockSystemLogger->expects($this->once())->method('logException');

		$requestHandler = $this->getAccessibleMock('F3\ExtJS\ExtDirect\RequestHandler', array('sendResponse'), array(), '', FALSE);
		$requestHandler->_set('requestBuilder', $mockRequestBuilder);
		$requestHandler->_set('dispatcher', $mockDispatcher);
		$requestHandler->_set('systemLogger', $mockSystemLogger);
		$requestHandler->handleRequest();
	}
}
?>