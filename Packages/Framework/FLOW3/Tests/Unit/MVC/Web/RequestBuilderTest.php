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
 * Testcase for the MVC Web Request Builder
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RequestBuilderTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * The mocked request
	 *
	 * @var \F3\FLOW3\MVC\Web\Request
	 */
	protected $mockRequest;

	/**
	 * @var \F3\FLOW3\Property\DataType\Uri
	 */
	protected $mockRequestUri;

	/**
	 * @var \F3\FLOW3\Utility\Environment
	 */
	protected $mockEnvironment;

	/**
	 * @var \F3\FLOW3\MVC\Web\Routing\RouterInterface
	 */
	protected $mockRouter;

	/**
	 * @var \F3\FLOW3\Configuration\ConfigurationManager
	 */
	protected $mockConfigurationManager;

	/**
	 * @var \F3\FLOW3\MVC\Web\RequestBuilder
	 */
	protected $builder;

	/**
	 * Sets up a request builder for testing
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function setUpRequestBuilder() {
		$this->mockRequestUri = $this->getMock('F3\FLOW3\Property\DataType\Uri', array(), array(), '', FALSE);
		$this->mockRequestUri->expects($this->once())->method('getArguments')->will($this->returnValue(array('someArgument' => 'GETArgument')));

		$this->mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$this->mockEnvironment->expects($this->any())->method('getRequestUri')->will($this->returnValue($this->mockRequestUri));

		$this->mockRequest = $this->getMock('F3\FLOW3\MVC\Web\Request', array(), array(), '', FALSE);
		$this->mockRequest->expects($this->any())->method('getRequestUri')->will($this->returnValue($this->mockRequestUri));

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->once())->method('create')->will($this->returnValue($this->mockRequest));

		$this->mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array('getConfiguration'), array(), '', FALSE);
		$this->mockConfigurationManager->expects($this->once())->method('getConfiguration')->will($this->returnValue(array()));

		$this->mockRouter = $this->getMock('F3\FLOW3\MVC\Web\Routing\RouterInterface', array('route', 'setRoutesConfiguration', 'resolve'));

		$this->builder = new \F3\FLOW3\MVC\Web\RequestBuilder();
		$this->builder->injectObjectManager($mockObjectManager);
		$this->builder->injectEnvironment($this->mockEnvironment);
		$this->builder->injectConfigurationManager($this->mockConfigurationManager);
		$this->builder->injectRouter($this->mockRouter);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildReturnsAWebRequestObject() {
		$this->setUpRequestBuilder();
		$this->assertSame($this->mockRequest, $this->builder->build());
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildInvokesTheRouteMethodOfTheRouter() {
		$this->setUpRequestBuilder();
		$this->mockRouter->expects($this->once())->method('route');
		$this->builder->build();
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildDetectsTheRequestMethodAndSetsItInTheRequestObject() {
		$this->setUpRequestBuilder();
		$this->mockEnvironment->expects($this->any())->method('getRequestMethod')->will($this->returnValue('GET'));
		$this->mockRequest->expects($this->once())->method('setMethod')->with($this->equalTo('GET'));
		$this->builder->build();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function buildSetGETArgumentsFromRequest() {
		$this->setUpRequestBuilder();
		$this->mockRequest->expects($this->once())->method('setArguments')->with(array('someArgument' => 'GETArgument'));
		$this->builder->build();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function buildSetsPOSTArgumentsFromRequest() {
		$this->setUpRequestBuilder();
		$this->mockRequest->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
		$this->mockEnvironment->expects($this->any())->method('getRawPostArguments')->will($this->returnValue(array('someArgument' => 'POSTArgument')));
		$this->mockEnvironment->expects($this->any())->method('getUploadedFiles')->will($this->returnValue(array()));
		$this->mockRequest->expects($this->once())->method('setArguments')->with(array('someArgument' => 'POSTArgument'));
		$this->builder->build();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setArgumentsFromRawRequestDataRecursivelyMergesGETAndPOSTArgumentsFromRequest() {
		$getArguments = array(
			'getArgument1' => 'getArgument1Value',
			'getArgument2' => array(
				'getArgument2a' => 'getArgument2aValue',
				'getArgument2b' => 'getArgument2bValue'
			),
			'argument3' => 'argument3Value',
			'argument4' => array(
				'argument4a' => 'argument4aValue',
				'argument4b' => array(
					'argument4ba' => 'argument4baValue',
				)
			),
			'argument5' => 'argument5Value',
		);

		$postArguments = array(
			'postArgument1' => 'postArgument1Value',
			'postArgument2' => array(
				'postArgument2a' => 'postArgument2aValue',
				'postArgument2b' => 'postArgument2bValue'
			),
			'argument3' => 'overriddenArgument3Value',
			'argument4' => array(
				'argument4a' => 'overriddenArgument4aValue',
				'argument4b' => array(
					'argument4bb' => 'argument4bbValue',
				),
				'argument4c' => 'argument4cValue',
			),
			'argument6' => 'argument6Value',
		);
		$expectedArguments = array(
			'getArgument1' => 'getArgument1Value',
			'getArgument2' => array(
				'getArgument2a' => 'getArgument2aValue',
				'getArgument2b' => 'getArgument2bValue'
			),
			'argument3' => 'overriddenArgument3Value',
			'argument4' => array(
				'argument4a' => 'overriddenArgument4aValue',
				'argument4b' => array(
					'argument4ba' => 'argument4baValue',
					'argument4bb' => 'argument4bbValue',
				),
				'argument4c' => 'argument4cValue',
			),
			'argument5' => 'argument5Value',
			'postArgument1' => 'postArgument1Value',
			'postArgument2' => array(
				'postArgument2a' => 'postArgument2aValue',
				'postArgument2b' => 'postArgument2bValue'
			),
			'argument6' => 'argument6Value',
		);

		$mockRequestUri = $this->getMock('F3\FLOW3\Property\DataType\Uri', array(), array(), '', FALSE);
		$mockRequestUri->expects($this->once())->method('getArguments')->will($this->returnValue($getArguments));

		$mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getRawPostArguments')->will($this->returnValue($postArguments));
		$mockEnvironment->expects($this->any())->method('getUploadedFiles')->will($this->returnValue(array()));

		$mockRequest = $this->getMock('F3\FLOW3\MVC\Web\Request', array('getMethod', 'getRequestUri'), array(), '', FALSE);
		$mockRequest->expects($this->any())->method('getRequestUri')->will($this->returnValue($mockRequestUri));
		$mockRequest->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));

		$builder = $this->getAccessibleMock('F3\FLOW3\MVC\Web\RequestBuilder', array('dummy'), array(), '', FALSE);
		$builder->injectEnvironment($mockEnvironment);
		$builder->_call('setArgumentsFromRawRequestData', $mockRequest);

		$this->assertSame($expectedArguments, $mockRequest->getArguments());
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setArgumentsFromRawRequestDataMergesUploadedFilesInformationIntoRequestArguments() {
		$uploadedFiles = array (
			'a0' => array (
				'a1' => array(
					'name' => 'a.txt',
					'type' => 'text/plain',
					'tmp_name' => '/private/var/tmp/phpbqXsYt',
					'error' => 0,
					'size' => 100,
				),
			),
			'd0' => array (
				'd1' => array(
					'd2' => array(
						'd3' => array(
							'name' => 'd.txt',
							'type' => 'text/plain',
							'tmp_name' => '/private/var/tmp/phprR3fax',
							'error' => 0,
							'size' => 400,
						),
					),
				),
			),
			'e0' => array (
				'e1' => array(
					'e2' => array(
						0 => array(
							'name' => 'e_one.txt',
							'type' => 'text/plain',
							'tmp_name' => '/private/var/tmp/php01fitB',
							'error' => 0,
							'size' => 510,
						)
					)
				)
			)
		);

		$postArguments = array(
			'a0' => array('a1POST' => 'postValue'),
			'e0' => array('e1' => array('e2' => 'will be overwritten'))
		);

		$expectedArguments = array (
			'a0' => array (
				'a1POST' => 'postValue',
				'a1' => array (
					'name' => 'a.txt',
					'type' => 'text/plain',
					'tmp_name' => '/private/var/tmp/phpbqXsYt',
					'error' => 0,
					'size' => 100,
				),
			),
			'e0' => array (
				'e1' => array (
					'e2' => array (
						0 => array (
							'name' => 'e_one.txt',
							'type' => 'text/plain',
							'tmp_name' => '/private/var/tmp/php01fitB',
							'error' => 0,
							'size' => 510,
						),
					),
				),
			),
			'd0' => array (
				'd1' => array (
					'd2' => array (
						'd3' => array (
							'name' => 'd.txt',
							'type' => 'text/plain',
							'tmp_name' => '/private/var/tmp/phprR3fax',
							'error' => 0,
							'size' => 400,
						)
					)
				)
			)
		);

		$mockRequestUri = $this->getMock('F3\FLOW3\Property\DataType\Uri', array(), array(), '', FALSE);
		$mockRequestUri->expects($this->once())->method('getArguments')->will($this->returnValue(array()));

		$mockEnvironment = $this->getMock('F3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getRawPostArguments')->will($this->returnValue($postArguments));
		$mockEnvironment->expects($this->any())->method('getUploadedFiles')->will($this->returnValue($uploadedFiles));

		$mockRequest = $this->getMock('F3\FLOW3\MVC\Web\Request', array('getMethod', 'getRequestUri'), array(), '', FALSE);
		$mockRequest->expects($this->any())->method('getRequestUri')->will($this->returnValue($mockRequestUri));
		$mockRequest->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));

		$builder = $this->getAccessibleMock('F3\FLOW3\MVC\Web\RequestBuilder', array('dummy'), array(), '', FALSE);
		$builder->injectEnvironment($mockEnvironment);
		$builder->_call('setArgumentsFromRawRequestData', $mockRequest);

		$this->assertSame($expectedArguments, $mockRequest->getArguments());
	}
}
?>