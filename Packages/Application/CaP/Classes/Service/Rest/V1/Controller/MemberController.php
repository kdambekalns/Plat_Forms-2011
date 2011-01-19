<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Service\Rest\V1\Controller;

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
 * REST Controller for Member
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @origin: M
 */
class MemberController extends \F3\FLOW3\MVC\Controller\RestController {

	/**
	 * @var string
	 */
	protected $resourceArgumentName = 'member';

	/**
	 * @var array
	 */
	protected $supportedFormats = array('json');

	/**
	 * @var array
	 */
	protected $viewFormatToObjectNameMap = array(
		 'json' => 'F3\FLOW3\MVC\View\JsonView',
	);

	/**
	 * @inject
	 * @var \F3\CaP\Domain\Repository\MemberRepository
	 */
	protected $memberRepository;

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
	 * Shows a single member
	 *
	 * @param \F3\CaP\Domain\Model\Member
	 * @return void
	 */
	public function showAction(\F3\CaP\Domain\Model\Member $member) {
		$memberArray = array(
			'id' => $member->getId(),
			'version' => $member->getVersion(),
			'username' => $member->getUsername(),
		);

		$loggedInAccount = $this->securityContext->getAccountByAuthenticationProviderName('RESTServiceProvider');
		if ($loggedInAccount !== NULL) {
			$loggedInMember = $loggedInAccount->getParty();
			if ($loggedInMember->isContactOf($member) || $loggedInMember === $member) {
				$memberArray += array(
					'id' => $member->getId(),
					'version' => $member->getVersion(),
					'username' => $member->getUsername(),
					'fullname' => $member->getFullName(),
					'email' => (string)$member->getPrimaryElectronicAddress(),
					'town' => $member->getTown(),
					'country' => $member->getCountry(),
					'gps' => $member->getLocationByCoordinates()
				);
			}
		}

		$this->view->assign('value', $memberArray);
	}


	/**
	 * Creates a new member
	 *
	 * @param array $member
	 * @return void
	 */
	public function createAction(array $member) {
		if (isset($member['fullname'])) {
			if (substr_count($member['fullname'], ' ') === 2) {
				list($member['firstName'], $member['middleName'], $member['lastName']) = explode(' ', $member['fullname']);
			} else {
				list($member['firstName'], $member['lastName']) = explode(' ', $member['fullname']);
			}
			unset ($member['fullname']);
		}

		if (!isset($member['username']) || !isset($member['password'])) {
			$this->throwStatus(400, 'Bad Request: Missing username or password');
		}
		if ($this->accountRepository->findByAccountIdentifier($member['username'])->count() > 0) {
			$this->throwStatus(400, 'Bad Request: Account already exists');
		}

		$restAccount = $this->accountFactory->createAccountWithPassword($member['username'], $member['password'], array('PortalMember'), 'RESTServiceProvider');
		$webAccount = $this->accountFactory->createAccountWithPassword($member['username'], $member['password'], array('PortalMember'), 'DefaultProvider');

		try {
			$member = $this->propertyMapper->map(array('email'), $member, 'F3\CaP\Domain\Model\Member', array('town', 'country', 'gps'));
			if ($member === FALSE) {
				$this->throwStatus(404);
			}
			$this->memberRepository->add($member);

			$member->addAccount($restAccount);
			$member->addAccount($webAccount);

			$this->accountRepository->add($restAccount);
			$this->accountRepository->add($webAccount);

			$this->forward('show', NULL, NULL, array('member' => $member));

		} catch (\InvalidArgumentException $exception) {
			$this->response->setStatus(400, 'Bad Request: ' . $exception);
		}
	}
}

?>