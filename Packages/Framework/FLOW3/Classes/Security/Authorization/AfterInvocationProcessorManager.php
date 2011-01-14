<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\Authorization;

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
 * The default after invocation manager that uses AfterInvocationProcessorInterface to process the return objects.
 * It resolves automatically any available AfterInvcocationProcessorInterface for the given return object and calls them.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AfterInvocationProcessorManager implements \F3\FLOW3\Security\Authorization\AfterInvocationManagerInterface {

	/**
	 * Processes the given return object. May throw an security exception or filter the result depending on the current user rights.
	 * It resolves any available AfterInvocationProcessor for the given return object and invokes them.
	 * The naming convention is: [InterceptedClassName]_[InterceptedMethodName]_AfterInvocationProcessor
	 *
	 *
	 * @param \F3\FLOW3\Security\Context $securityContext The current securit context
	 * @param object $object The return object to be processed
	 * @param \F3\FLOW3\AOP\JoinPointInterface $joinPoint The joinpoint of the returning method
	 * @return boolean TRUE if access is granted, FALSE if the manager abstains from decision
	 * @throws \F3\FLOW3\Security\Exception\AccessDeniedException If access is not granted
	 * @todo processors must also be configurable
	 */
	public function process(\F3\FLOW3\Security\Context $securityContext, $object, \F3\FLOW3\AOP\JoinPointInterface $joinPoint) {

	}

	/**
	 * Returns TRUE if a appropriate after invocation processor is available to process return objects of the given classname
	 *
	 * @param string $className The classname that should be checked
	 * @return boolean TRUE if this access decision manager can decide on objects with the given classname
	 */
	public function supports($className) {

	}
}

?>