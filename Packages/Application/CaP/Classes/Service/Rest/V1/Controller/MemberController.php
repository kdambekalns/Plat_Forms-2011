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

			// TODO: if is contact of ...

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

		$this->view->assign('value', $memberArray);
	}
}

?>