<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "CaP".                        *
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
 * Registration controller for the CaP package
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @origin RM
 */
class RegistrationController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\AccountRepository
	*/
	protected $accountRepository;

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\AccountFactory
	 */
	protected $accountFactory;

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
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->forward('new');
	}

	/**
	 * Renders a form for creating a new member + account.
	 *
	 * @param \F3\CaP\Domain\Model\Member $newMember
	 * @dontvalidate $newMember
	 * @return void
	 */
	public function newAction(\F3\CaP\Domain\Model\Member $newMember = NULL) {
		$this->view->assign('newMember', $newMember);
	}

	/**
	 * Create account action
	 *
	 * @param \F3\CaP\Domain\Model\Member $newMember The new member to add
	 * @param string $username The username to authenticate this member
	 * @param string $password The password to authenticate this member
	 * @return void
	 */
	public function createAction(\F3\CaP\Domain\Model\Member $newMember, $username, $password) {
		$existingAccount = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, 'DefaultProvider');
		if (count($existingAccount) > 0) {
			$this->flashMessageContainer->add('The username is already taken, please choose another username.');
			$referrer = $this->request->getArgument('__referrer');
			$this->forward($referrer['actionName'], $referrer['controllerName'], $referrer['packageKey'], $this->request->getArguments());
		}

		$RESTAccount = $this->accountFactory->createAccountWithPassword($username, $password, array('PortalMember'), 'RESTServiceProvider');
		$WebAccount = $this->accountFactory->createAccountWithPassword($username, $password, array('PortalMember'), 'DefaultProvider');
		$newMember->addAccount($RESTAccount);
		$newMember->addAccount($WebAccount);

		$this->accountRepository->add($RESTAccount);
		$this->accountRepository->add($WebAccount);

		$authenticationTokens = $this->securityContext->getAuthenticationTokensOfType('\F3\FLOW3\Security\Authentication\Token\UsernamePassword');
		if (count($authenticationTokens) === 1) {
			$authenticationTokens[0]->setAccount($WebAccount);
			$authenticationTokens[0]->setAuthenticationStatus(\F3\FLOW3\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
		}

		$this->redirect('show', 'Member', NULL, array('member' => $newMember));
	}
}

?>