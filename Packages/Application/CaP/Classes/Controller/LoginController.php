<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The Login Controller
 *
 * @version $Id: LoginController.php 4022 2010-03-29 15:14:07Z robert $
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @origin RM
 */
class LoginController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \F3\FLOW3\Security\Account
	 */
	protected $account;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeAction() {
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				$this->account = $token->getAccount();
			}
		}
	}

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * @param \F3\FLOW3\MVC\View\ViewInterface $view The view to be initialized
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeView(\F3\FLOW3\MVC\View\ViewInterface $view) {
		$view->assign('account', $this->account);
	}

	/**
	 * Displays the login screen
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function indexAction() {
	}

	/**
	 * Authenticates an account by invoking the Provider based Authentication Manager.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function authenticateAction() {
		try {
			$this->authenticationManager->authenticate();
			$this->redirect('index');
		} catch (\F3\FLOW3\Security\Exception\AuthenticationRequiredException $exception) {
			$this->flashMessageContainer->add('Wrong username or password.');
			throw $exception;
		}
	}

	/**
	 * Logs out
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function logoutAction() {
		$this->authenticationManager->logout();
		$this->flashMessageContainer->add('Successfully logged out.');
		$this->redirect('index');
	}
}

?>