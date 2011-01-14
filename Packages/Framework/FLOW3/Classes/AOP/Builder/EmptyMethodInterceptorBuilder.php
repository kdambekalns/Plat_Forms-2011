<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\AOP\Builder;

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
 * A AOP method interceptor code builder which generates an empty method as used for
 * introductions without advices delivering the implementation of the introduced method.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class EmptyMethodInterceptorBuilder extends \F3\FLOW3\AOP\Builder\AbstractMethodInterceptorBuilder {

	/**
	 * Builds PHP code for an empty method
	 *
	 * @param string $methodName Name of the method to build an interceptor for
	 * @param array $interceptedMethods An array of method names and their meta information, including advices for the method (if any)
	 * @param string $targetClassName Name of the target class to build the interceptor for
	 * @param array
	 * @return string PHP code of the interceptor
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function build($methodName, array $interceptedMethods, $targetClassName) {
		if ($methodName === '__construct') throw new \F3\FLOW3\AOP\Exception('The ' . __CLASS__ . ' cannot build constructor interceptor code.', 1173112554);

		$declaringClassName = $interceptedMethods[$methodName]['declaringClassName'];

		$methodDocumentation = $this->buildMethodDocumentation($declaringClassName, $methodName);
		$methodParametersCode = $this->buildMethodParametersCode($declaringClassName, $methodName, TRUE);

		$staticKeyword = $this->reflectionService->isMethodStatic($declaringClassName, $methodName) ? 'static ' : '';

		$emptyInterceptorCode = '
	/**
	 * Placeholder for the method ' . $methodName . '() declared in
	 * ' . $declaringClassName . '.
	 * ' . $methodDocumentation . '
	 * @return void
	 */
	' . $staticKeyword . 'public function ' . $methodName . '(' . $methodParametersCode . ') {}
';
		return $emptyInterceptorCode;
	}

}

?>