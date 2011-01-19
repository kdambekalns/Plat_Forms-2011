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
class AttendeeController extends \F3\FLOW3\MVC\Controller\RestController {

	/**
	 * @var string
	 */
	protected $resourceArgumentName = 'attendee';

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
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * Shows a list of attendees of a specified conference
	 *
	 * @param \F3\CaP\Domain\Model\Conference $conference
	 * @return void
	 */
	public function listAction(\F3\CaP\Domain\Model\Conference $conference) {
		$attendeesArray = array();

		foreach ($conference->getAttendees() as $member) {
			$attendeesArray[] = array(
				'id' => $member->getId(),
				'version' => $member->getVersion(),
				'username' => $member->getUsername(),
				'details' => $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('member' => $member), 'Member'),
			);
		}

		$this->view->assign('value', $attendeesArray);

		if (count($attendeesArray) === 0) {
			$this->response->setStatus(204);
		}
	}

	/**
	 * Adds an attendee to the given conference
	 *
	 * @param \F3\CaP\Domain\Model\Conference $conference
	 * @param array $attendee
	 * @return void
	 */
	public function createAction(\F3\CaP\Domain\Model\Conference $conference, array $attendee) {
		if (!isset($attendee['username'])) {
			$this->throwStatus(400);
		}

		$loggedInAccount = $this->securityContext->getAccountByAuthenticationProviderName('RESTServiceProvider');
		if ($loggedInAccount === NULL) {
			$this->throwStatus(500);
		}

		if ($loggedInAccount->getAccountIdentifier() !== $attendee['username']) {
			$this->throwStatus(403);
		}

		$conference->addAttendee($loggedInAccount->getParty());
		$this->response->setStatus(204);
	}

	/**
	 * Removes an attendee from the given conference
	 *
	 * @param \F3\CaP\Domain\Model\Conference $conference
	 * @param \F3\CaP\Domain\Model\Member $attendee
	 * @return void
	 */
	public function deleteAction(\F3\CaP\Domain\Model\Conference $conference, \F3\CaP\Domain\Model\Member $attendee) {
		$loggedInAccount = $this->securityContext->getAccountByAuthenticationProviderName('RESTServiceProvider');
		if ($loggedInAccount === NULL) {
			$this->throwStatus(500);
		}

		if ($loggedInAccount->getParty() !== $attendee) {
			$this->throwStatus(403);
		}

		$conference->removeAttendee($attendee);
		$this->response->setStatus(204);
	}

}

?>