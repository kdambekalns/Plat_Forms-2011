<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Object\Configuration;

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
 * Testcase for the object configuration builder
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ConfigurationBuilderTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function allBasicOptionsAreSetCorrectly() {
		$factoryObjectName = uniqid('ConfigurationBuilderTest');
		eval('class ' . $factoryObjectName . ' { public function manufacture() {} } ');

		$configurationArray = array();
		$configurationArray['scope'] = 'prototype';
		$configurationArray['className'] = __CLASS__;
		$configurationArray['factoryObjectName'] = $factoryObjectName;
		$configurationArray['factoryMethodName'] = 'manufacture';
		$configurationArray['lifecycleInitializationMethodName'] = 'initializationMethod';
		$configurationArray['lifecycleShutdownMethodName'] = 'shutdownMethod';
		$configurationArray['autowiring'] = 'off';

		$objectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('TestObject', __CLASS__);
		$objectConfiguration->setScope(\F3\FLOW3\Object\Configuration\Configuration::SCOPE_PROTOTYPE);
		$objectConfiguration->setClassName(__CLASS__);
		$objectConfiguration->setFactoryObjectName($factoryObjectName);
		$objectConfiguration->setFactoryMethodName('manufacture');
		$objectConfiguration->setLifecycleInitializationMethodName('initializationMethod');
		$objectConfiguration->setLifecycleShutdownMethodName('shutdownMethod');
		$objectConfiguration->setAutowiring(\F3\FLOW3\Object\Configuration\Configuration::AUTOWIRING_MODE_OFF);

		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__);
		$this->assertEquals($objectConfiguration, $builtObjectConfiguration, 'The manually created and the built object configuration don\'t match.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function argumentsOfTypeObjectCanSpecifyAdditionalObjectConfigurationOptions() {
		$configurationArray = array();
		$configurationArray['arguments'][1]['object']['name'] = 'Foo';
		$configurationArray['arguments'][1]['object']['className'] = __CLASS__;

		$argumentObjectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('Foo', __CLASS__);
		$argumentObjectConfiguration->setConfigurationSourceHint(__CLASS__ . ' / argument "1"');

		$objectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('TestObject', 'TestObject');
		$objectConfiguration->setArgument(new \F3\FLOW3\Object\Configuration\ConfigurationArgument(1, $argumentObjectConfiguration, \F3\FLOW3\Object\Configuration\ConfigurationArgument::ARGUMENT_TYPES_OBJECT));

		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__);
		$this->assertEquals($objectConfiguration, $builtObjectConfiguration);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function propertiesOfTypeObjectCanSpecifyAdditionalObjectConfigurationOptions() {
		$configurationArray = array();
		$configurationArray['properties']['theProperty']['object']['name'] = 'Foo';
		$configurationArray['properties']['theProperty']['object']['className'] = __CLASS__;

		$propertyObjectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('Foo', __CLASS__);
		$propertyObjectConfiguration->setConfigurationSourceHint(__CLASS__ . ' / property "theProperty"');

		$objectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('TestObject', 'TestObject');
		$objectConfiguration->setProperty(new \F3\FLOW3\Object\Configuration\ConfigurationProperty('theProperty', $propertyObjectConfiguration, \F3\FLOW3\Object\Configuration\ConfigurationProperty::PROPERTY_TYPES_OBJECT));

		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__);
		$this->assertEquals($objectConfiguration, $builtObjectConfiguration);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function itIsPossibleToPassArraysAsStraightArgumentOrPropertyValues() {
		$configurationArray = array();
		$configurationArray['properties']['straightValueProperty']['value'] = array('foo' => 'bar', 'object' => 'nö');
		$configurationArray['arguments'][1]['value'] = array('foo' => 'bar', 'object' => 'nö');

		$objectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('TestObject', 'TestObject');
		$objectConfiguration->setProperty(new \F3\FLOW3\Object\Configuration\ConfigurationProperty('straightValueProperty', array('foo' => 'bar', 'object' => 'nö')));
		$objectConfiguration->setArgument(new \F3\FLOW3\Object\Configuration\ConfigurationArgument(1, array('foo' => 'bar', 'object' => 'nö')));

		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__);
		$this->assertEquals($objectConfiguration, $builtObjectConfiguration);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function settingsCanBeInjectedAsArgumentOrProperty() {
		$configurationArray = array();
		$configurationArray['arguments'][1]['setting'] = 'F3.Foo.Bar';
		$configurationArray['properties']['someProperty']['setting'] = 'F3.Bar.Baz';

		$objectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('TestObject', 'TestObject');
		$objectConfiguration->setArgument(new \F3\FLOW3\Object\Configuration\ConfigurationArgument(1, 'F3.Foo.Bar', \F3\FLOW3\Object\Configuration\ConfigurationProperty::PROPERTY_TYPES_SETTING));
		$objectConfiguration->setProperty(new \F3\FLOW3\Object\Configuration\ConfigurationProperty('someProperty', 'F3.Bar.Baz', \F3\FLOW3\Object\Configuration\ConfigurationProperty::PROPERTY_TYPES_SETTING));

		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__);
		$this->assertEquals($objectConfiguration, $builtObjectConfiguration);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function existingObjectConfigurationIsUsedIfSpecified() {
		$configurationArray = array();
		$configurationArray['scope'] = 'prototype';
		$configurationArray['properties']['firstProperty'] = 'straightValue';

		$objectConfiguration = new \F3\FLOW3\Object\Configuration\Configuration('TestObject', __CLASS__);

		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__, $objectConfiguration);
		$this->assertSame($objectConfiguration, $builtObjectConfiguration, 'The returned object configuration object is not the one we passed to the builder.');
	}

	/**
	 * @test
	 * @expectedException \F3\FLOW3\Object\Exception\InvalidObjectConfigurationException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function invalidOptionResultsInException() {
		$configurationArray = array('scoopy' => 'prototype');
		$builtObjectConfiguration = \F3\FLOW3\Object\Configuration\ConfigurationBuilder::buildFromConfigurationArray('TestObject', $configurationArray, __CLASS__);
	}
}
?>