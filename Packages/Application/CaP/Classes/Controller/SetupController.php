<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "CaP".                        *
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
 * The Setup Controller, which creates the initial admin user
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @origin: M
 */
class SetupController extends \F3\FLOW3\MVC\Controller\ActionController {

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
	 * Creates the admin/password admin account

	 * @return void
	 */
	public function indexAction() {
		$this->accountRepository->removeAll();
		$this->memberRepository->removeAll();

		$siteAccount = $this->accountFactory->createAccountWithPassword('admin', 'password', array('PortalAdmin', 'PortalMember'), 'DefaultProvider');
		$restAccount = $this->accountFactory->createAccountWithPassword('admin', 'password', array('PortalAdmin', 'PortalMember'), 'RESTServiceProvider');

		$electronicAddress = $this->objectManager->create('F3\Party\Domain\Model\ElectronicAddress');
		$electronicAddress->setIdentifier('admin@localhost');
		$electronicAddress->setType(\F3\Party\Domain\Model\ElectronicAddress::TYPE_EMAIL);

		$member = $this->objectManager->create('F3\CaP\Domain\Model\Member');
		$member->setFirstName('Plat');
		$member->setMiddleName('Forms');
		$member->setLastName('Administrator');
		$member->setPrimaryElectronicAddress($electronicAddress);
		$member->addAccount($siteAccount);
		$member->addAccount($restAccount);
		$member->setTown('Nürnberg');
		$member->setCountry('Germany');

		$this->accountRepository->add($siteAccount);
		$this->accountRepository->add($restAccount);

		return 'Created the admin/password account';
	}
}

?>