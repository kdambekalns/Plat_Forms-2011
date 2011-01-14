<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Security\Policy;

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
 * Testcase for for the policy service
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PolicyServiceTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function thePolicyIsLoadedCorrectlyFromTheConfigurationManager() {
		$mockPolicyExpressionParser = $this->getMock('F3\FLOW3\Security\Policy\PolicyExpressionParser', array(), array(), '', FALSE);

		$policy = array(
			'roles' => array('THE_ROLE' => array()),
			'resources' => array(
				'methods' => array('theResource' => 'method(Foo->bar())'),
				'entities' => array()
			),
			'acls' => array(
				'theRole' => array(
					'methods' => array(
						'theMethodResource' => 'GRANT'
					),
					'entities' => array(
						'theEntityResource' => 'GRANT'
					)
				)
			)
		);

		$mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array(), array(), '', FALSE);
		$mockConfigurationManager->expects($this->once())->method('getConfiguration')->with(\F3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_POLICY)->will($this->returnValue($policy));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->any())->method('has')->will($this->returnValue(FALSE));

		$policyService = new \F3\FLOW3\Security\Policy\PolicyService();
		$policyService->injectCache($mockCache);
		$policyService->injectConfigurationManager($mockConfigurationManager);
		$policyService->injectPolicyExpressionParser($mockPolicyExpressionParser);

		$policyService->initializeObject();
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function initializeObjectSetsTheEverybodyRoleInThePolicy() {
		$mockPolicyExpressionParser = $this->getMock('F3\FLOW3\Security\Policy\PolicyExpressionParser', array(), array(), '', FALSE);

		$policy = array(
			'roles' => array(),
			'resources' => array(
				'methods' => array(),
				'entities' => array()
			),
			'acls' => array()
		);

		$mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array(), array(), '', FALSE);
		$mockConfigurationManager->expects($this->once())->method('getConfiguration')->with(\F3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_POLICY)->will($this->returnValue($policy));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->any())->method('has')->will($this->returnValue(FALSE));

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('parseEntityAcls'), array(), '', FALSE);
		$policyService->expects($this->once())->method('parseEntityAcls')->will($this->returnValue(array()));
		$policyService->injectCache($mockCache);
		$policyService->injectConfigurationManager($mockConfigurationManager);
		$policyService->injectPolicyExpressionParser($mockPolicyExpressionParser);

		$policyService->initializeObject();

		$expectedPolicy = array(
			'roles' => array('Everybody' => array()),
			'resources' => array(
				'methods' => array(),
				'entities' => array()
			),
			'acls' => array(
				'Everybody' => array(
					'methods' => array(),
					'entities' => array()
				)
			)
		);

		$this->assertEquals($expectedPolicy, $policyService->_get('policy'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function initializeObjectAddsTheAbstainPrivilegeForTheEverybodyRoleToEveryResourceWhereNoOtherPrivilegeIsSetInThePolicy() {
		$mockPolicyExpressionParser = $this->getMock('F3\FLOW3\Security\Policy\PolicyExpressionParser', array(), array(), '', FALSE);

		$policy = array(
			'roles' => array(),
			'resources' => array(
				'methods' => array(
					'methodResource1' => 'expression',
					'methodResource2' => 'expression',
				),
				'entities' => array(
					'class1' => array(
						'entityResource1' => 'expression'
					),
					'class2' => array(
						'entityResource2' => 'expression'
					)
				)
			),
			'acls' => array('Everybody' => array(
				'methods' => array(
					'methodResource2' => 'GRANT'
				),
				'entities' => array(
					'entityResource2' => 'DENY',
				)
			))
		);

		$mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array(), array(), '', FALSE);
		$mockConfigurationManager->expects($this->once())->method('getConfiguration')->with(\F3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_POLICY)->will($this->returnValue($policy));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->any())->method('has')->will($this->returnValue(FALSE));

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('parseEntityAcls'), array(), '', FALSE);
		$policyService->expects($this->once())->method('parseEntityAcls')->will($this->returnValue(array()));
		$policyService->injectCache($mockCache);
		$policyService->injectConfigurationManager($mockConfigurationManager);
		$policyService->injectPolicyExpressionParser($mockPolicyExpressionParser);

		$policyService->initializeObject();

		$expectedPolicy = array(
			'roles' => array('Everybody' => array()),
			'resources' => array(
				'methods' => array(
					'methodResource1' => 'expression',
					'methodResource2' => 'expression',
				),
				'entities' => array(
					'class1' => array(
						'entityResource1' => 'expression'
					),
					'class2' => array(
						'entityResource2' => 'expression'
					)
				)
			),
			'acls' => array('Everybody' => array(
				'methods' => array(
					'methodResource2' => 'GRANT',
					'methodResource1' => 'ABSTAIN',
				),
				'entities' => array(
					'entityResource2' => 'DENY',
					'entityResource1' => 'ABSTAIN',
				)
			))
		);

		$this->assertEquals($expectedPolicy, $policyService->_get('policy'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function matchesAsksThePolicyExpressionParserToBuildPointcutFiltersForMethodResourcesAndChecksIfTheyMatchTheGivenClassAndMethod() {
		$settings = array(
			'security' => array(
				'enable' => TRUE
			)
		);

		$policy = array(
			'roles' => array('TheRole' => array()),
			'resources' => array(
				'methods' => array('theResource' => 'method(Foo->bar())'),
				'entities' => array()
			),
			'acls' => array('TheRole' => array('methods' => array('theResource' => 'GRANT')))
		);

		$mockFilter = $this->getMock('F3\FLOW3\AOP\Pointcut\PointcutFilterComposite', array(), array(), '', FALSE);
		$mockFilter->expects($this->once())->method('matches')->with('Foo', 'bar', 'Baz')->will($this->returnValue(TRUE));

		$mockPolicyExpressionParser = $this->getMock('F3\FLOW3\Security\Policy\PolicyExpressionParser', array(), array(), '', FALSE);
		$mockPolicyExpressionParser->expects($this->once())->method('parseMethodResources')->with('theResource', $policy['resources']['methods'])->will($this->returnValue($mockFilter));

		$accessibleProxyClassName = $this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService');
		$policyService = new $accessibleProxyClassName();
		$policyService->injectPolicyExpressionParser($mockPolicyExpressionParser);
		$policyService->injectSettings($settings);
		$policyService->_set('policy', $policy);

		$this->assertTrue($policyService->matches('Foo', 'bar', 'Baz', 1));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function matchesAddsRuntimeEvaluationsCorrectlyToTheInternalPolicyCache() {
		$settings = array(
			'security' => array(
				'enable' => TRUE
			)
		);

		$policy = array(
			'acls' => array('TheRole' => array(
                'methods' => array(
                    'FirstResource' => 'GRANT',
                    'SecondResource' => 'DENY',
                    'ThirdResource' => 'DENY'
                )
			))
		);

		$mockFilter1 = $this->getMock('F3\FLOW3\AOP\Pointcut\PointcutFilterComposite', array(), array(), '', FALSE);
		$mockFilter1->expects($this->once())->method('matches')->with('Foo', 'bar', 'Baz')->will($this->returnValue(TRUE));
		$mockFilter1->expects($this->once())->method('hasRuntimeEvaluationsDefinition')->will($this->returnValue(TRUE));
		$mockFilter1->expects($this->once())->method('getRuntimeEvaluationsClosureCode')->will($this->returnValue('closureCode1'));

		$mockFilter2 = $this->getMock('F3\FLOW3\AOP\Pointcut\PointcutFilterComposite', array(), array(), '', FALSE);
		$mockFilter2->expects($this->once())->method('matches')->with('Foo', 'bar', 'Baz')->will($this->returnValue(TRUE));
		$mockFilter2->expects($this->once())->method('hasRuntimeEvaluationsDefinition')->will($this->returnValue(FALSE));
		$mockFilter2->expects($this->never())->method('getRuntimeEvaluationsClosureCode');

		$mockFilter3 = $this->getMock('F3\FLOW3\AOP\Pointcut\PointcutFilterComposite', array(), array(), '', FALSE);
		$mockFilter3->expects($this->once())->method('matches')->with('Foo', 'bar', 'Baz')->will($this->returnValue(TRUE));
		$mockFilter3->expects($this->once())->method('hasRuntimeEvaluationsDefinition')->will($this->returnValue(TRUE));
		$mockFilter3->expects($this->once())->method('getRuntimeEvaluationsClosureCode')->will($this->returnValue('closureCode3'));

		$filters = array(
			'TheRole' => array(
				'FirstResource' => $mockFilter1,
				'SecondResource' => $mockFilter2,
				'ThirdResource' => $mockFilter3
			)
		);

		$accessibleProxyClassName = $this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService');
		$policyService = new $accessibleProxyClassName();
		$policyService->injectSettings($settings);
		$policyService->_set('policy', $policy);
		$policyService->_set('filters', $filters);

		$policyService->matches('Foo', 'bar', 'Baz', 1);

		$expectedACLCache = array(
			'Foo->bar' => array(
				'TheRole' => array(
					'FirstResource' => array(
						'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT,
						'runtimeEvaluationsClosureCode' => 'closureCode1'
					),
					'SecondResource' => array(
						'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY,
						'runtimeEvaluationsClosureCode' => FALSE
					),
					'ThirdResource' => array(
						'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY,
						'runtimeEvaluationsClosureCode' => 'closureCode3'
					)
				)
			)
		);

		$this->assertEquals($policyService->_get('acls'), $expectedACLCache);
	}

	/**
	 * @test
	 * @category unit
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function matchesAlwaysReturnsFalseIfSecurityIsDisabled() {
		$settings = array('security' => array('enable' => FALSE));

		$policyService = new \F3\FLOW3\Security\Policy\PolicyService();
		$policyService->injectSettings($settings);
		$this->assertFalse($policyService->matches('Foo', 'bar', 'Baz', 1));
	}

	/**
	 * @test
	 * @category unit
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function matchesStoresMatchedPoliciesInAnArrayForLaterCaching() {
		$settings = array(
			'security' => array(
				'enable' => TRUE
				)
		);

		$policy = array(
			'roles' => array('theRole' => array()),
			'resources' => array(
				'methods' => array('theResource' => 'method(Foo->bar())'),
				'entities' => array()
			),
			'acls' => array('theRole' => array('methods' => array('theResource' => 'GRANT')))
		);

		$mockFilter = $this->getMock('F3\FLOW3\AOP\Pointcut\PointcutFilterComposite', array(), array(), '', FALSE);
		$mockFilter->expects($this->once())->method('matches')->with('Foo', 'bar', 'Baz')->will($this->returnValue(TRUE));

		$mockPolicyExpressionParser = $this->getMock('F3\FLOW3\Security\Policy\PolicyExpressionParser', array(), array(), '', FALSE);
		$mockPolicyExpressionParser->expects($this->once())->method('parseMethodResources')->with('theResource', $policy['resources']['methods'])->will($this->returnValue($mockFilter));

		$accessibleProxyClassName = $this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService');
		$policyService = new $accessibleProxyClassName();
		$policyService->injectPolicyExpressionParser($mockPolicyExpressionParser);
		$policyService->injectSettings($settings);
		$policyService->_set('policy', $policy);

		$policyService->matches('Foo', 'bar', 'Baz', 1);

		$expectedPolicies = array(
			'Foo->bar' => array(
				'theRole' => array(
					'theResource' => array (
						'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT,
                        'runtimeEvaluationsClosureCode' => FALSE
                    )
				)
			)
		);

		$aclsReflection = new \ReflectionProperty($policyService, 'acls');
		$this->assertSame($expectedPolicies, $aclsReflection->getValue($policyService));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegesForJoinPointReturnsAnEmptyArrayIfNoPrivilegesCouldBeFound() {
		$mockJoinPoint = $this->getMock('F3\FLOW3\AOP\JoinPointInterface', array(), array(), '', FALSE);
		$mockJoinPoint->expects($this->once())->method('getClassName')->will($this->returnValue('className'));
		$mockJoinPoint->expects($this->once())->method('getMethodName')->will($this->returnValue('methodName'));

		$policyService = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService'), array('dummy'), array(), '', FALSE);
		$policyService->_set('acls', array('className->methodName' => array()));

		$this->assertEquals(array(), $policyService->getPrivilegesForJoinPoint($this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE), $mockJoinPoint));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegesForJoinPointReturnsThePrivilegesArrayThatHasBeenParsedForTheGivenJoinPointAndRole() {
		$mockJoinPoint = $this->getMock('F3\FLOW3\AOP\JoinPointInterface', array(), array(), '', FALSE);
		$mockJoinPoint->expects($this->once())->method('getClassName')->will($this->returnValue('className'));
		$mockJoinPoint->expects($this->once())->method('getMethodName')->will($this->returnValue('methodName'));

		$mockRole = $this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE);
		$mockRole->expects($this->once())->method('__toString')->will($this->returnValue('role1'));

		$privilegesArray = array('FirstResource' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT, 'SecondResource' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY, 'ThirdResource' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT);

		$aclsCache = array(
						'className->methodName' =>
							array(
								'role1' => array(
									'FirstResource' => array(
										'runtimeEvaluationsClosureCode' => FALSE,
										'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
									),
									'SecondResource' => array(
										'runtimeEvaluationsClosureCode' => FALSE,
										'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
									),
									'ThirdResource' => array(
										'runtimeEvaluationsClosureCode' => FALSE,
										'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
									)
								)
							)
						);

		$policyService = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService'), array('dummy'), array(), '', FALSE);
		$policyService->_set('acls', $aclsCache);

		$this->assertEquals($privilegesArray, $policyService->getPrivilegesForJoinPoint($mockRole, $mockJoinPoint));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegesForJoinPointReturnsOnlyPrivilgesThatPassedRuntimeEvaluationsInThePrivilegesArrayThatHasBeenParsedForTheGivenJoinPointAndRole() {
		$mockJoinPoint = $this->getMock('F3\FLOW3\AOP\JoinPointInterface', array(), array(), '', FALSE);
		$mockJoinPoint->expects($this->once())->method('getClassName')->will($this->returnValue('className'));
		$mockJoinPoint->expects($this->once())->method('getMethodName')->will($this->returnValue('methodName'));

		$mockRole = $this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE);
		$mockRole->expects($this->once())->method('__toString')->will($this->returnValue('role1'));

		$privilegesArray = array('SecondResource' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT);

		$aclsCache = array(
						'className->methodName' => array(
								'role1' => array(
									'FirstResource' => array(
										'runtimeEvaluationsClosureCode' => 'function () { return FALSE; };',
										'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
									),
									'SecondResource' => array(
										'runtimeEvaluationsClosureCode' => 'function () { return TRUE; };',
										'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
									)
								)
							)
						);

		$policyService = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService'), array('dummy'), array(), '', FALSE);
		$policyService->_set('acls', $aclsCache);

		$this->assertEquals($privilegesArray, $policyService->getPrivilegesForJoinPoint($mockRole, $mockJoinPoint));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegeForResourceReturnsThePrivilegeThatHasBeenParsedForTheGivenResource() {
		$mockRole = $this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE);
		$mockRole->expects($this->once())->method('__toString')->will($this->returnValue('role1'));

		$aclsCache = array(
						'someResource' => array(
								'role1' => array(
									'runtimeEvaluationsClosureCode' => FALSE,
									'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
								)
							)
						);

		$policyService = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService'), array('dummy'), array(), '', FALSE);
		$policyService->_set('acls', $aclsCache);

		$this->assertEquals(\F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT, $policyService->getPrivilegeForResource($mockRole, 'someResource'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegeForResourceReturnsADenyPrivilegeIfTheResourceHasRuntimeEvaluationsDefined() {
		$mockRole = $this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE);
		$mockRole->expects($this->once())->method('__toString')->will($this->returnValue('role1'));

		$aclsCache = array(
						'someResource' => array(
								'role1' => array(
									'runtimeEvaluationsClosureCode' => 'function () { return TRUE; };',
									'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
								)
							)
						);

		$policyService = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService'), array('dummy'), array(), '', FALSE);
		$policyService->_set('acls', $aclsCache);

		$this->assertEquals(\F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY, $policyService->getPrivilegeForResource($mockRole, 'someResource'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegeForResourceReturnsNullIfTheGivenRoleHasNoPriviligesDefinedForTheGivenResource() {
		$mockRole = $this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE);
		$mockRole->expects($this->once())->method('__toString')->will($this->returnValue('role2'));

		$aclsCache = array(
						'someResource' => array(
								'role1' => array(
									'runtimeEvaluationsClosureCode' => 'function () { return TRUE; };',
									'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
								)
							)
						);

		$policyService = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService'), array('dummy'), array(), '', FALSE);
		$policyService->_set('acls', $aclsCache);

		$this->assertNull($policyService->getPrivilegeForResource($mockRole, 'someResource'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegeForResourceReturnsADenyPrivilegeIfAskedForAResourceThatIsNotConnectedToAPolicyEntry() {
		$mockRole = $this->getMock('F3\FLOW3\Security\Policy\Role', array(), array(), '', FALSE);
		$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');

		$policyServiceClassName = $this->buildAccessibleProxy('F3\FLOW3\Security\Policy\PolicyService');
		$policyService = new $policyServiceClassName();
		$policyService->injectObjectManager($mockObjectManager);

		$policyService->_set('acls', array());
		$policyService->_set('resources', array('someResourceNotConnectedToAPolicyEntry' => 'someDefinition'));

		$this->assertEquals(\F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY, $policyService->getPrivilegeForResource($mockRole, 'someResourceNotConnectedToAPolicyEntry'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function initializeObjectLoadsTheEntityConstraintsFromTheCache() {
		$mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array(), array(), '', FALSE);

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->at(0))->method('has')->with('acls')->will($this->returnValue(TRUE));
		$mockCache->expects($this->at(1))->method('get')->with('acls')->will($this->returnValue(array('cachedAcls')));
		$mockCache->expects($this->at(2))->method('has')->with('entityResourcesConstraints')->will($this->returnValue(TRUE));
		$mockCache->expects($this->at(3))->method('get')->with('entityResourcesConstraints')->will($this->returnValue(array('cachedConstraints')));

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('setAclsForEverybodyRole'), array(), '', FALSE);
		$policyService->injectCache($mockCache);
		$policyService->injectConfigurationManager($mockConfigurationManager);

		$policyService->initializeObject();

		$this->assertEquals($policyService->_get('entityResourcesConstraints'), array('cachedConstraints'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function initializeObjectCallsThePolicyExpressionPraserAndBuildsTheEntityConstraintsIfTheCacheIsEmpty() {
		$policy = array(
			'resources' => array(
				'methods' => array(),
				'entities' => array('firstEntity', 'secondEntity')
			)
		);

		$mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array(), array(), '', FALSE);
		$mockConfigurationManager->expects($this->once())->method('getConfiguration')->with(\F3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_POLICY)->will($this->returnValue($policy));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->at(0))->method('has')->with('acls')->will($this->returnValue(TRUE));
		$mockCache->expects($this->at(1))->method('get')->with('acls')->will($this->returnValue(array('cachedAcls')));
		$mockCache->expects($this->at(2))->method('has')->with('entityResourcesConstraints')->will($this->returnValue(FALSE));

		$mockPolicyExpressionParser = $this->getMock('F3\FLOW3\Security\Policy\PolicyExpressionParser', array(), array(), '', FALSE);
		$mockPolicyExpressionParser->expects($this->once())->method('parseEntityResources')->with(array('firstEntity', 'secondEntity'))->will($this->returnValue(array('newParsedConstraints')));

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('setAclsForEverybodyRole'), array(), '', FALSE);
		$policyService->injectCache($mockCache);
		$policyService->injectConfigurationManager($mockConfigurationManager);
		$policyService->injectPolicyExpressionParser($mockPolicyExpressionParser);

		$policyService->initializeObject();

		$this->assertEquals($policyService->_get('entityResourcesConstraints'), array('newParsedConstraints'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function initializeObjectCallsParseEntityAclsIfTheAclCacheIsEmpty() {
		$mockConfigurationManager = $this->getMock('F3\FLOW3\Configuration\ConfigurationManager', array(), array(), '', FALSE);
		$mockConfigurationManager->expects($this->once())->method('getConfiguration')->with(\F3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_POLICY)->will($this->returnValue(array()));

		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->at(0))->method('has')->with('acls')->will($this->returnValue(FALSE));
		$mockCache->expects($this->at(1))->method('has')->with('entityResourcesConstraints')->will($this->returnValue(TRUE));
		$mockCache->expects($this->at(2))->method('get')->with('entityResourcesConstraints')->will($this->returnValue(array('cachedConstraints')));

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('parseEntityAcls', 'setAclsForEverybodyRole'), array(), '', FALSE);
		$policyService->expects($this->once())->method('parseEntityAcls');

		$policyService->injectCache($mockCache);
		$policyService->injectConfigurationManager($mockConfigurationManager);

		$policyService->initializeObject();
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function parseEntityAclsParsesTheEntityAclsCorrectly() {
		$policy = array(
			'acls' => array(
				'theRole' => array(
					'entities' => array(
						'theEntityResource' => 'GRANT',
						'theOtherEntityResource' => 'DENY'
					)
				),
				'theOtherRole' => array(
					'entities' => array(
						'theEntityResource' => 'DENY',
						'theOtherEntityResource' => 'GRANT'
					)
				)
			)
		);

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('dummy'), array(), '', FALSE);
		$policyService->_set('policy', $policy);

		$policyService->_call('parseEntityAcls');

		$expectedAcls = array(
			'theEntityResource' => array(
				'theRole' => array('privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT),
				'theOtherRole' => array('privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY)
			),
			'theOtherEntityResource' => array(
				'theRole' => array('privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY),
				'theOtherRole' => array('privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT)
			)
		);

		$this->assertEquals($expectedAcls, $policyService->_get('acls'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function savePolicyCacheStoresTheEntityConstraintsAndACLsCorrectlyInTheCache() {
		$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->at(0))->method('has')->with('acls')->will($this->returnValue(FALSE));
		$mockCache->expects($this->at(1))->method('set')->with('acls', array('aclsArray'), array('F3_FLOW3_AOP'));
		$mockCache->expects($this->at(2))->method('has')->with('entityResourcesConstraints')->will($this->returnValue(FALSE));
		$mockCache->expects($this->at(3))->method('set')->with('entityResourcesConstraints', array('entityResourcesConstraintsArray'));

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('buildEntityConstraints'), array(), '', FALSE);
		$policyService->injectCache($mockCache);
		$policyService->_set('acls', array('aclsArray'));
		$policyService->_set('entityResourcesConstraints', array('entityResourcesConstraintsArray'));

		$policyService->savePolicyCache();
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getResourcesConstraintsForEntityTypeAndRolesBasicallyWorks() {
		$entityResourcesConstraints = array(
			'F3_MyEntity' => array(
				'resource1' => 'constraint1',
				'resource2' => 'constraint2',
				'resource3' => 'constraint3'
			)
		);

		$acls = array(
			'resource1' => array(
				'Administrator' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			),
			'resource2' => array(
				'SomeOtherRole' => array(
                    'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			),
			'resource3' => array(
				'Customer' => array(
                    'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			)
		);

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('buildEntityConstraints'), array(), '', FALSE);
		$policyService->_set('entityResourcesConstraints', $entityResourcesConstraints);
		$policyService->_set('acls', $acls);

		$result = $policyService->getResourcesConstraintsForEntityTypeAndRoles('F3\MyEntity', array('Customer', 'Administrator'));

		$this->assertEquals($result, array('resource1' => 'constraint1', 'resource3' => 'constraint3'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getResourcesConstraintsForEntityTypeAndRolesDoesNotReturnConstraintsForResourcesThatGotADenyAndAGrantPrivilege() {
		$entityResourcesConstraints = array(
			'F3_MyEntity' => array(
				'resource1' => 'constraint1',
				'resource2' => 'constraint2',
				'resource3' => 'constraint3'
			)
		);

		$acls = array(
			'resource1' => array(
				'Administrator' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
				),
				'Customer' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				)
			),
			'resource2' => array(
				'SomeOtherRole' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			),
			'resource3' => array(
				'Customer' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			)
		);

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('buildEntityConstraints'), array(), '', FALSE);
		$policyService->_set('entityResourcesConstraints', $entityResourcesConstraints);
		$policyService->_set('acls', $acls);

		$result = $policyService->getResourcesConstraintsForEntityTypeAndRoles('F3\MyEntity', array('Customer', 'Administrator'));

		$this->assertEquals($result, array('resource3' => 'constraint3'));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function hasPolicyEntryForEntityTypeWorks() {
		$entityResourcesConstraints = array(
			'F3_MyEntity' => array(
				'resource1' => 'constraint1',
				'resource2' => 'constraint2',
				'resource3' => 'constraint3'
			)
		);

		$acls = array(
			'resource1' => array(
				'Administrator' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT
				),
				'Customer' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				)
			),
			'resource2' => array(
				'SomeOtherRole' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			),
			'resource3' => array(
				'Customer' => array(
					'privilege' => \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_DENY
				),
			)
		);

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('buildEntityConstraints'), array(), '', FALSE);
		$policyService->_set('entityResourcesConstraints', $entityResourcesConstraints);
		$policyService->_set('acls', $acls);

		$this->assertTrue($policyService->hasPolicyEntryForEntityType('F3\MyEntity', array('Manager', 'Administrator', 'Anonymous')));
		$this->assertTrue($policyService->hasPolicyEntryForEntityType('F3\MyEntity', array('Manager', 'Customer')));
		$this->assertFalse($policyService->hasPolicyEntryForEntityType('F3\MyOtherEntity', array('Manager', 'Administrator', 'Anonymous')));
		$this->assertFalse($policyService->hasPolicyEntryForEntityType('F3\MyOtherEntity', array('Manager', 'Customer')));
		$this->assertFalse($policyService->hasPolicyEntryForEntityType('F3\MyEntity', array('Manager', 'Anonymous')));
		$this->assertFalse($policyService->hasPolicyEntryForEntityType('F3\MyEntity', array('Manager', 'King')));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAllParentRolesUnnestsRoleInheritanceCorrectly() {
		$policy = array(
			'roles' => array(
				'Manager' => array(),
				'Administrator' => array('Chief', 'Manager'),
				'Customer' => array(),
				'User' => array('Customer'),
				'Employee' => array('Administrator', 'User'),
				'Chief' => array()
			),
			'resources' => array(),
			'acls' => array()
		);

		$policyService = $this->getAccessibleMock('F3\FLOW3\Security\Policy\PolicyService', array('dummy'), array(), '', FALSE);
		$policyService->_set('policy', $policy);

		$expectedResult = array(
			'Manager' => new \F3\FLOW3\Security\Policy\Role('Manager'),
			'Administrator' => new \F3\FLOW3\Security\Policy\Role('Administrator'),
			'Customer' => new \F3\FLOW3\Security\Policy\Role('Customer'),
			'User' => new \F3\FLOW3\Security\Policy\Role('User'),
			'Chief' => new \F3\FLOW3\Security\Policy\Role('Chief'),
		);

		$result = $policyService->getAllParentRoles(new \F3\FLOW3\Security\Policy\Role('Employee'));

		sort($expectedResult);
		sort($result);

		$this->assertEquals($result, $expectedResult);
	}
}
?>
