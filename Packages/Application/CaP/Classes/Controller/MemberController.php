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
 * Member controller for the CaP package
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @origin M
 */
class MemberController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @inject
	 * @var F3\CaP\Domain\Repository\MemberRepository
	 */
	protected $memberRepository;

	/**
	 * @inject
	 * @var F3\CaP\Domain\Repository\ContactRequestRepository
	 */
	protected $contactRequestRepository;

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
	 * Lists all available members
	 *
	 * @param array $filter
	 * @return void
	 */
	public function indexAction(array $filter = array()) {
		$members = $this->memberRepository->findWithFilter($filter);
		$this->view->assign('filter', $filter);
		$this->view->assign('members', $members);
		$contactRequested = new \SplObjectStorage();
		foreach ($members as $member) {
			if ($this->account->getParty()->hasContactRequestSentTo($member)) $contactRequested->attach($member);
		}
		$this->view->assign('membersWithContactRequest', $contactRequested);
	}

	/**
	 * Index action
	 *
	 * @param \F3\CaP\Domain\Model\Member $member The member to show
	 * @return void
	 */
	public function showAction(\F3\CaP\Domain\Model\Member $member) {
		$this->view->assign('member', $member);
		$this->view->assign('contactRequests', $this->contactRequestRepository->findOpenByReceiver($member));
	}

	/**
	 * Sends a contact request to the given receiver
	 *
	 * @param \F3\CaP\Domain\Model\Member $receiver
	 * @param array $currentSearchFilter
	 * @return void
	 */
	public function sendContactRequestAction(\F3\CaP\Domain\Model\Member $receiver, array $currentSearchFilter = array()) {
		$contactRequest = $this->objectManager->create('F3\CaP\Domain\Model\ContactRequest');
		$contactRequest->setSender($this->account->getParty());
		$contactRequest->setReceiver($receiver);
		$contactRequest->setStatus(\F3\CaP\Domain\Model\ContactRequest::RCD_REQUESTED);
		$this->contactRequestRepository->add($contactRequest);

		$this->redirect('index', NULL, NULL, array('filter' => $currentSearchFilter));
	}
}

?>