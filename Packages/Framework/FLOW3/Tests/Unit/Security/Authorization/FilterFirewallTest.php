<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Security\Authorization;

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
 * Testcase for the filter firewall
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class FilterFirewallTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function configuredFiltersAreCreatedCorrectly() {
		$resolveRequestPatternClassCallback = function() {
			$args = func_get_args();

			if ($args[0] === 'URI') return 'mockPatternURI';
			elseif ($args[0] === 'F3\TestRequestPattern') return 'mockPatternTest';
		};

		$resolveInterceptorClassCallback = function() {
			$args = func_get_args();

			if ($args[0] === 'AccessGrant') return 'mockInterceptorAccessGrant';
			elseif ($args[0] === 'F3\TestSecurityInterceptor') return 'mockInterceptorTest';
		};

		$mockRequestPattern1 = $this->getMock('F3\FLOW3\Security\RequestPatternInterface', array(), array(), 'pattern1', FALSE);
		$mockRequestPattern1->expects($this->once())->method('setPattern')->with('/some/url/.*');
		$mockRequestPattern2 = $this->getMock('F3\FLOW3\Security\RequestPatternInterface', array(), array(), 'pattern2', FALSE);
		$mockRequestPattern2->expects($this->once())->method('setPattern')->with('/some/url/blocked.*');

		$getObjectCallback = function() use (&$mockRequestPattern1, &$mockRequestPattern2) {
			$args = func_get_args();

			if ($args[0] === 'mockPatternURI') return $mockRequestPattern1;
			elseif ($args[0] === 'mockPatternTest') return $mockRequestPattern2;
			elseif ($args[0] === 'mockInterceptorAccessGrant') return 'AccessGrant';
			elseif ($args[0] === 'mockInterceptorTest') return 'InterceptorTest';

			elseif ($args[0] === 'F3\FLOW3\Security\Authorization\RequestFilter') {
				if ($args[1] == $mockRequestPattern1 && $args[2] === 'AccessGrant') return 'filter1';
				if ($args[1] == $mockRequestPattern2 && $args[2] === 'InterceptorTest') return 'filter2';
			}
		};

		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->any())->method('get')->will($this->returnCallback($getObjectCallback));
		$mockPatternResolver = $this->getMock('F3\FLOW3\Security\RequestPatternResolver', array(), array(), '', FALSE);
		$mockPatternResolver->expects($this->any())->method('resolveRequestPatternClass')->will($this->returnCallback($resolveRequestPatternClassCallback));
		$mockInterceptorResolver = $this->getMock('F3\FLOW3\Security\Authorization\InterceptorResolver', array(), array(), '', FALSE);
		$mockInterceptorResolver->expects($this->any())->method('resolveInterceptorClass')->will($this->returnCallback($resolveInterceptorClassCallback));

		$settings = array(
			array(
				'patternType' => 'URI',
				'patternValue' => '/some/url/.*',
				'interceptor' => 'AccessGrant'
			),
			array(
				'patternType' => 'F3\TestRequestPattern',
				'patternValue' => '/some/url/blocked.*',
				'interceptor' => 'F3\TestSecurityInterceptor'
			)
		);

		$firewall = $this->getAccessibleMock('F3\FLOW3\Security\Authorization\FilterFirewall', array('blockIllegalRequests'), array(), '', FALSE);
		$firewall->_set('objectManager', $mockObjectManager);
		$firewall->_set('requestPatternResolver', $mockPatternResolver);
		$firewall->_set('interceptorResolver', $mockInterceptorResolver);

		$firewall->_call('buildFiltersFromSettings', $settings);
		$result = $firewall->_get('filters');

		$this->assertEquals(array('filter1', 'filter2'), $result, 'The filters were not built correctly.');
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function allConfiguredFiltersAreCalled() {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\Web\Request');

		$mockFilter1 = $this->getMock('F3\FLOW3\Security\Authorization\RequestFilter', array(), array(), '', FALSE);
		$mockFilter1->expects($this->once())->method('filterRequest')->with($mockRequest);
		$mockFilter2 = $this->getMock('F3\FLOW3\Security\Authorization\RequestFilter', array(), array(), '', FALSE);
		$mockFilter2->expects($this->once())->method('filterRequest')->with($mockRequest);
		$mockFilter3 = $this->getMock('F3\FLOW3\Security\Authorization\RequestFilter', array(), array(), '', FALSE);
		$mockFilter3->expects($this->once())->method('filterRequest')->with($mockRequest);

		$firewall = $this->getAccessibleMock('F3\FLOW3\Security\Authorization\FilterFirewall', array('dummy'), array(), '', FALSE);
		$firewall->_set('filters', array($mockFilter1, $mockFilter2, $mockFilter3));

		$firewall->blockIllegalRequests($mockRequest);
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @expectedException \F3\FLOW3\Security\Exception\AccessDeniedException
	 */
	public function ifRejectAllIsSetAndNoFilterExplicitlyAllowsTheRequestAPermissionDeniedExceptionIsThrown() {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\Web\Request');

		$mockFilter1 = $this->getMock('F3\FLOW3\Security\Authorization\RequestFilter', array(), array(), '', FALSE);
		$mockFilter1->expects($this->once())->method('filterRequest')->with($mockRequest)->will($this->returnValue(FALSE));
		$mockFilter2 = $this->getMock('F3\FLOW3\Security\Authorization\RequestFilter', array(), array(), '', FALSE);
		$mockFilter2->expects($this->once())->method('filterRequest')->with($mockRequest)->will($this->returnValue(FALSE));
		$mockFilter3 = $this->getMock('F3\FLOW3\Security\Authorization\RequestFilter', array(), array(), '', FALSE);
		$mockFilter3->expects($this->once())->method('filterRequest')->with($mockRequest)->will($this->returnValue(FALSE));

		$firewall = $this->getAccessibleMock('F3\FLOW3\Security\Authorization\FilterFirewall', array('dummy'), array(), '', FALSE);
		$firewall->_set('filters', array($mockFilter1, $mockFilter2, $mockFilter3));
		$firewall->_set('rejectAll', TRUE);

		$firewall->blockIllegalRequests($mockRequest);
	}
}
?>