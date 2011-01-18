<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\Authentication\Token;

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
 * An authentication token used for simple username and password authentication via HTTP Basic Auth.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @origin M
 */
class UsernamePasswordHTTPBasic extends \F3\FLOW3\Security\Authentication\Token\UsernamePassword {

	/**
	 * Updates the username and password credentials from the POST vars, if the POST parameters
	 * are available. Sets the authentication status to REAUTHENTICATION_NEEDED, if credentials have been sent.
	 *
	 * @param \F3\FLOW3\MVC\RequestInterface $request The current request instance
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function updateCredentials(\F3\FLOW3\MVC\RequestInterface $request) {
		$username = NULL;
		$password = NULL;
		$requestHeaders = $this->environment->getRequestHeaders();

		if (isset($requestHeaders['User']) && isset($requestHeaders['Pw'])) {
			$username = $requestHeaders['User'];
			$password = $requestHeaders['Pw'];
		} else {
			$this->credentials = array();
			$this->authenticationStatus = \F3\FLOW3\Security\Authentication\TokenInterface::NO_CREDENTIALS_GIVEN;
		}

		if (!empty($username) && !empty($password)) {
			$this->credentials['username'] = $username;
			$this->credentials['password'] = $password;

			$this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
		}
	}
}

?>