<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Reflection;

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
 */
/**
 * Provides methods to call appropriate getter/setter on an object given the
 * property name. It does this following these rules:
 * - if the target object is an instance of ArrayAccess, it gets/sets the property
 * - if public getter/setter method exists, call it.
 * - if public property exists, return/set the value of it.
 * - else, throw exception
 *
 * Some methods support arrays as well, most notably getProperty() and
 * getPropertyPath().
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ObjectAccess {

	const ACCESS_GET = 0;
	const ACCESS_SET = 1;
	const ACCESS_PUBLIC = 2;

	/**
	 * Get a property of a given object or array.
	 *
	 * Tries to get the property the following ways:
	 * - if the target is an array, and has this property, we return it.
	 * - if public getter method exists, call it.
	 * - if the target object is an instance of ArrayAccess, it gets the property
	 *   on it if it exists.
	 * - if public property exists, return the value of it.
	 * - else, throw exception
	 *
	 * @param mixed $subject Object or array to get the property from
	 * @param string $propertyName name of the property to retrieve
	 * @return object Value of the property.
	 * @throws \InvalidArgumentException in case $subject was not an object or $propertyName was not a string
	 * @throws \F3\FLOW3\Reflection\Exception\PropertyNotAccessibleException if the property was not accessible
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	static public function getProperty($subject, $propertyName) {
		if (!is_object($subject) && !is_array($subject)) {
			throw new \InvalidArgumentException('$subject must be an object or array, ' . gettype($subject). ' given.', 1237301367);
		}
		if (!is_string($propertyName) && (!is_array($subject) && !$subject instanceof \ArrayAccess)) {
			throw new \InvalidArgumentException('Given property name is not of type string.', 1231178303);
		}

		if (is_array($subject)) {
			if (array_key_exists($propertyName, $subject)) {
				return $subject[$propertyName];
			}
		} else {
			if (is_callable(array($subject, 'get' . ucfirst($propertyName)))) {
				return call_user_func(array($subject, 'get' . ucfirst($propertyName)));
			} elseif (is_callable(array($subject, 'is' . ucfirst($propertyName)))) {
				return call_user_func(array($subject, 'is' . ucfirst($propertyName)));
			} elseif ($subject instanceof \ArrayAccess && isset($subject[$propertyName])) {
				return $subject[$propertyName];
			} elseif (array_key_exists($propertyName, get_object_vars($subject))) {
				return $subject->$propertyName;
			}
		}

		throw new \F3\FLOW3\Reflection\Exception\PropertyNotAccessibleException('The property "' . $propertyName . '" on the subject was not accessible.', 1263391473);
	}

	/**
	 * Gets a property path from a given object or array.
	 * 
	 * If propertyPath is "bla.blubb", then we first call getProperty($object, 'bla'),
	 * and on the resulting object we call getProperty(..., 'blubb').
	 *
	 * For arrays the keys are checked likewise.
	 *
	 * If $evaluateClosures is TRUE, then the following happens:
	 * In case a property on the path is a Closure, the Closure is executed,
	 * and the return value of the closure is used for further processing.
	 *
	 * @param mixed $subject An object or array
	 * @param string $propertyPath
	 * @param boolean $evaluateClosures If set to TRUE, closures along the path will be evaluated and their return value will be used.
	 * @return mixed Value of the property
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	static public function getPropertyPath($subject, $propertyPath, $evaluateClosures = FALSE) {
		$propertyPathSegments = explode('.', $propertyPath);
		foreach ($propertyPathSegments as $pathSegment) {
			if (is_object($subject) && self::isPropertyGettable($subject, $pathSegment)) {
				$subject = self::getProperty($subject, $pathSegment);
			} elseif ((is_array($subject) || $subject instanceof \ArrayAccess) && isset($subject[$pathSegment])) {
				$subject = $subject[$pathSegment];
			} else {
				return NULL;
			}

			if ($evaluateClosures && $subject instanceof \Closure) {
				$subject = $subject();
			}
		}
		return $subject;
	}

	/**
	 * Set a property for a given object.
	 * Tries to set the property the following ways:
	 * - if public setter method exists, call it.
	 * - if public property exists, set it directly.
	 * - if the target object is an instance of ArrayAccess, it sets the property
	 *   on it without checking if it existed.
	 * - else, return FALSE
	 *
	 * @param mixed $subject The target object or array
	 * @param string $propertyName Name of the property to set
	 * @param object $propertyValue Value of the property
	 * @return boolean TRUE if the property could be set, FALSE otherwise
	 * @throws \InvalidArgumentException in case $object was not an object or $propertyName was not a string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	static public function setProperty(&$subject, $propertyName, $propertyValue) {
		if (is_array($subject)) {
			$subject[$propertyName] = $propertyValue;
			return TRUE;
		}

		if (!is_object($subject)) throw new \InvalidArgumentException('subject must be an object or array, ' . gettype($subject). ' given.', 1237301368);
		if (!is_string($propertyName)) throw new \InvalidArgumentException('Given property name is not of type string.', 1231178878);

		if (is_callable(array($subject, $setterMethodName = self::buildSetterMethodName($propertyName)))) {
			call_user_func(array($subject, $setterMethodName), $propertyValue);
		} elseif ($subject instanceof \ArrayAccess) {
			$subject[$propertyName] = $propertyValue;
		} elseif (array_key_exists($propertyName, get_object_vars($subject))) {
			$subject->$propertyName = $propertyValue;
		} else {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Returns an array of properties which can be get with the getProperty()
	 * method.
	 * Includes the following properties:
	 * - which can be get through a public getter method.
	 * - public properties which can be directly get.
	 *
	 * @param object $object Object to receive property names for
	 * @return array Array of all gettable property names
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	static public function getGettablePropertyNames($object) {
		if (!is_object($object)) throw new \InvalidArgumentException('$object must be an object, ' . gettype($object). ' given.', 1237301369);
		if ($object instanceof \stdClass) {
			$declaredPropertyNames = array_keys(get_object_vars($object));
		} else {
			$declaredPropertyNames = array_keys(get_class_vars(get_class($object)));
		}

		foreach (get_class_methods($object) as $methodName) {
			if (is_callable(array($object, $methodName))) {
				if (substr($methodName, 0, 2) === 'is') {
					$declaredPropertyNames[] = lcfirst(substr($methodName, 2));
				}
				if (substr($methodName, 0, 3) === 'get') {
					$declaredPropertyNames[] = lcfirst(substr($methodName, 3));
				}
			}
		}

		$propertyNames = array_unique($declaredPropertyNames);
		sort($propertyNames);
		return $propertyNames;
	}

	/**
	 * Returns an array of properties which can be set with the setProperty()
	 * method.
	 * Includes the following properties:
	 * - which can be set through a public setter method.
	 * - public properties which can be directly set.
	 *
	 * @param object $object Object to receive property names for
	 * @return array Array of all settable property names
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	static public function getSettablePropertyNames($object) {
		if (!is_object($object)) throw new \InvalidArgumentException('$object must be an object, ' . gettype($object). ' given.', 1264022994);
		if ($object instanceof \stdClass) {
			$declaredPropertyNames = array_keys(get_object_vars($object));
		} else {
			$declaredPropertyNames = array_keys(get_class_vars(get_class($object)));
		}

		foreach (get_class_methods($object) as $methodName) {
			if (substr($methodName, 0, 3) === 'set' && is_callable(array($object, $methodName))) {
				$declaredPropertyNames[] = lcfirst(substr($methodName, 3));
			}
		}

		$propertyNames = array_unique($declaredPropertyNames);
		sort($propertyNames);
		return $propertyNames;
	}

	/**
	 * Tells if the value of the specified property can be set by this Object Accessor.
	 *
	 * @param object $object Object containting the property
	 * @param string $propertyName Name of the property to check
	 * @return boolean
	 * @author Robert Lemke <robert@typo3.org>
	 */
	static public function isPropertySettable($object, $propertyName) {
		if (!is_object($object)) throw new \InvalidArgumentException('$object must be an object, ' . gettype($object). ' given.', 1259828920);
		if ($object instanceof \stdClass && array_search($propertyName, array_keys(get_object_vars($object))) !== FALSE) {
			return TRUE;
		} elseif (array_search($propertyName, array_keys(get_class_vars(get_class($object)))) !== FALSE) {
			return TRUE;
		}
		return is_callable(array($object, self::buildSetterMethodName($propertyName)));
	}

	/**
	 * Tells if the value of the specified property can be retrieved by this Object Accessor.
	 *
	 * @param object $object Object containting the property
	 * @param string $propertyName Name of the property to check
	 * @return boolean
	 * @author Robert Lemke <robert@typo3.org>
	 */
	static public function isPropertyGettable($object, $propertyName) {
		if (!is_object($object)) throw new \InvalidArgumentException('$object must be an object, ' . gettype($object). ' given.', 1259828921);
		if ($object instanceof \stdClass && array_search($propertyName, array_keys(get_object_vars($object))) !== FALSE) {
			return TRUE;
		} elseif (array_search($propertyName, array_keys(get_class_vars(get_class($object)))) !== FALSE) {
			return TRUE;
		}
		if (is_callable(array($object, 'get' . ucfirst($propertyName)))) return TRUE;
		return is_callable(array($object, 'is' . ucfirst($propertyName)));
	}

	/**
	 * Get all properties (names and their current values) of the current
	 * $object that are accessible through this class.
	 *
	 * @param object $object Object to get all properties from.
	 * @return array Associative array of all properties.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo What to do with ArrayAccess
	 */
	static public function getGettableProperties($object) {
		if (!is_object($object)) throw new \InvalidArgumentException('$object must be an object, ' . gettype($object). ' given.', 1237301370);
		$properties = array();
		foreach (self::getGettablePropertyNames($object) as $propertyName) {
			$properties[$propertyName] = self::getProperty($object, $propertyName);
		}
		return $properties;
	}

	/**
	 * Build the setter method name for a given property by capitalizing the
	 * first letter of the property, and prepending it with "set".
	 *
	 * @param string $propertyName Name of the property
	 * @return string Name of the setter method name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	static public function buildSetterMethodName($propertyName) {
		return 'set' . ucfirst($propertyName);
	}
}

?>