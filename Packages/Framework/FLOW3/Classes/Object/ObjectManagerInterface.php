<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Object;

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
 * Interface for the TYPO3 Object Manager
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
interface ObjectManagerInterface {

	/**
	 * Sets the Object ObjectManager to a specific context. All operations related to objects
	 * will be carried out based on the configuration for the current context.
	 *
	 * The context should be set as early as possible, preferably before any object has been
	 * instantiated.
	 *
	 * By default the context is set to "default". Although the context can be freely chosen,
	 * the following contexts are explicitly supported by FLOW3:
	 * "Production", "Development", "Testing", "Profiling", "Staging"
	 *
	 * @param  string $context Name of the context
	 * @return void
	 */
	public function setContext($context);

	/**
	 * Returns the name of the currently set context.
	 *
	 * @return  string Name of the current context
	 * @api
	 */
	public function getContext();

	/**
	 * Returns a fresh or existing instance of the object specified by $objectName.
	 *
	 * Important:
	 *
	 * If possible, instances of Prototype objects should always be created with the
	 * Object Manager's create() method and Singleton objects should rather be
	 * injected by some type of Dependency Injection.
	 *
	 * @param string $objectName The name of the object to return an instance of
	 * @return object The object instance
	 * @api
	 */
	public function get($objectName);

	/**
	 * Creates a fresh instance of the object specified by $objectName.
	 *
	 * This factory method can only create objects of the scope prototype.
	 * Singleton objects must be either injected by some type of Dependency Injection or
	 * if that is not possible, be retrieved by the get() method of the
	 * Object Manager
	 *
	 * You must use either Dependency Injection or this factory method for instantiation
	 * of your objects if you need FLOW3's object management capabilities (including
	 * AOP, Security and Persistence). It is absolutely okay and often advisable to
	 * use the "new" operator for instantiation in your automated tests.
	 *
	 * @param string $objectName The name of the object to create
	 * @return object The new object instance
	 * @throws \InvalidArgumentException if the object name starts with a backslash
	 * @throws \F3\FLOW3\Object\Exception\UnknownObjectException if an object with the given name does not exist
	 * @throws \F3\FLOW3\Object\Exception\WrongScopeException if the specified object is not configured as Prototype
	 * @since 1.0.0 alpha 8
	 * @api
	 */
	public function create($objectName);

	/**
	 * Creates an instance of the specified object without calling its constructor.
	 * Subsequently reinjects the object's dependencies.
	 *
	 * This method is mainly used by the persistence and the session sub package.
	 *
	 * Note: The object must be of scope prototype or session which means that
	 *       the object container won't store an instance of the recreated object.
	 *
	 * @param string $objectName Name of the object to create a skeleton for
	 * @return object The recreated, uninitialized (ie. w/ uncalled constructor) object
	 */
	public function recreate($objectName);

	/**
	 * Returns TRUE if an object with the given name has already
	 * been registered.
	 *
	 * @param  string $objectName Name of the object
	 * @return boolean TRUE if the object has been registered, otherwise FALSE
	 * @since 1.0.0 alpha 8
	 * @api
	 */
	public function isRegistered($objectName);

	/**
	 * Returns the case sensitive object name of an object specified by a
	 * case insensitive object name. If no object of that name exists,
	 * FALSE is returned.
	 *
	 * In general, the case sensitive variant is used everywhere in FLOW3,
	 * however there might be special situations in which the
	 * case sensitive name is not available. This method helps you in these
	 * rare cases.
	 *
	 * @param  string $caseInsensitiveObjectName The object name in lower-, upper- or mixed case
	 * @return mixed Either the mixed case object name or FALSE if no object of that name was found.
	 * @api
	 */
	public function getCaseSensitiveObjectName($caseInsensitiveObjectName);

	/**
	 * Returns the object name corresponding to a given class name.
	 *
	 * @param string $className The class name
	 * @return string The object name corresponding to the given class name
	 * @api
	 */
	public function getObjectNameByClassName($className);

	/**
	 * Returns the implementation class name for the specified object
	 *
	 * @param string $objectName The object name
	 * @return string The class name corresponding to the given object name or FALSE if no such object is registered
	 * @api
	 */
	public function getClassNameByObjectName($objectName);

	/**
	 * Returns the scope of the specified object.
	 *
	 * @param string $objectName The object name
	 * @return integer One of the Configuration::SCOPE_ constants
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getScope($objectName);

	/**
	 * Discards the cached Static Object Container in order to rebuild it on the
	 * next script run.
	 *
	 * This method is called by a signal emitted when files change.
	 *
	 * @param string $signalName Name of the signal which triggered this method
	 * @param string $monitorIdentifier Identifier of the file monitor
	 * @param array $changedFiles Path and file name of the changed files
	 * @return void
	 */
	public function flushStaticObjectContainer($signalName, $monitorIdentifier, array $changedFiles);

	/**
	 * Initializes the session scope of the object container
	 *
	 * @return void
	 */
	public function initializeSession();

	/**
	 * Returns TRUE if the session has been initialized
	 *
	 * @return boolean TRUE if the session has been initialized
	 */
	public function isSessionInitialized();

	/**
	 * Shuts the object manager down and calls the shutdown methods of all objects
	 * which are configured for it.
	 *
	 * @return void
	 */
	public function shutdown();

	/**
	 * Returns a fresh or existing instance of the object specified by $objectName.
	 *
	 * Important:
	 *
	 * If possible, instances of Prototype objects should always be created with the
	 * Object Factory's create() method and Singleton objects should rather be
	 * injected by some type of Dependency Injection.
	 *
	 * @param string $objectName The name of the object to return an instance of
	 * @return object The object instance
	 * @deprecated since 1.0.0 alpha 8
	 */
	public function getObject($objectName);

	/**
	 * Returns TRUE if an object with the given name has already
	 * been registered.
	 *
	 * @param  string $objectName Name of the object
	 * @return boolean TRUE if the object has been registered, otherwise FALSE
	 * @deprecated since 1.0.0 alpha 8
	 */
	public function isObjectRegistered($objectName);

}

?>