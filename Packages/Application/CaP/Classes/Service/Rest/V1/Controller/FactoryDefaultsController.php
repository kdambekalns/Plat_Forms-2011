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
 * REST Controller for Factory Defaults
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @origin: M
 */
class FactoryDefaultsController extends \F3\FLOW3\MVC\Controller\RestController {

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
	 * @var \F3\CaP\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @inject
	 * @var \F3\CaP\Domain\Repository\ConferenceRepository
	 */
	protected $conferenceRepository;

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
	 * Forwards to the importAction
	 *
	 * @return void
	 */
	public function listAction() {
		if ($this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName('admin', 'RESTServiceProvider') === NULL) {
			$this->redirect('index');
		}

		$this->forward('import');
	}

	/**
	 * Resets the whole application to the initial data provided by FactoryDefaults.json
	 *
	 * @return void
	 */
	public function importAction() {
		$this->accountRepository->removeAll();
		$this->memberRepository->removeAll();
		$this->categoryRepository->removeAll();
		$this->conferenceRepository->removeAll();

		$factoryDefaults = json_decode(file_get_contents('resource://CaP/Private/FactoryDefaults.json'));
		foreach ($factoryDefaults->member as $memberRecord) {
			$siteAccount = $this->accountFactory->createAccountWithPassword($memberRecord->username, $memberRecord->password, array('PortalUser'), 'DefaultProvider');
			$restAccount = $this->accountFactory->createAccountWithPassword($memberRecord->username, $memberRecord->password, array('PortalUser'), 'RESTServiceProvider');
			$this->accountRepository->add($siteAccount);
			$this->accountRepository->add($restAccount);

			if (substr_count($memberRecord->fullname, ' ') === 2) {
				list($firstName, $middleName, $lastName) = explode(' ', $memberRecord->fullname);
			} else {
				list($firstName, $lastName) = explode(' ', $memberRecord->fullname);
				$middleName = '';
			}
			$name = $this->objectManager->create('F3\Party\Domain\Model\PersonName', '', $firstName, $middleName, $lastName);

			$electronicAddress = $this->objectManager->create('F3\Party\Domain\Model\ElectronicAddress');
			$electronicAddress->setIdentifier(trim($memberRecord->email));
			$electronicAddress->setType(\F3\Party\Domain\Model\ElectronicAddress::TYPE_EMAIL);

			$address = $this->objectManager->create('F3\Party\Domain\Model\Address');
			$address->setLocality($memberRecord->town);
			$address->setCountry($memberRecord->country);
			$address->setLocationByCoordinates($memberRecord->gps);

			$member = $this->objectManager->create('F3\CaP\Domain\Model\Member');
			$member->setName($name);
			$member->setPrimaryElectronicAddress($electronicAddress);
			$member->addAccount($siteAccount);
			$member->addAccount($restAccount);
			$member->setAddress($address);

			$this->memberRepository->add($member);

		}

		$categories = array();
		foreach ($factoryDefaults->category as $categoryRecord) {
			$categories[$categoryRecord->name] = $this->objectManager->create('F3\CaP\Domain\Model\Category', $categoryRecord->name);
			$this->categoryRepository->add($categories[$categoryRecord->name]);
		}
		foreach ($factoryDefaults->category as $categoryRecord) {
			foreach ($categoryRecord->subcategories as $subCategoryRecord) {
				$categories[$subCategoryRecord->name]->setParent($categories[$categoryRecord->name]);
			}
		}

		foreach ($factoryDefaults->conference as $conferenceRecord) {
			$conference = $this->objectManager->create('F3\CaP\Domain\Model\Conference');
			$conference->setName($conferenceRecord->name);
			$conference->setDescription($conferenceRecord->description);

			$conference->setStartDate(\F3\CaP\Utility\DateConverter::createDateFromString($conferenceRecord->startdate));
			$conference->setEndDate(\F3\CaP\Utility\DateConverter::createDateFromString($conferenceRecord->enddate));

			$conferenceCategories = new \SplObjectStorage();
			foreach ($conferenceRecord->categories as $categoryRecord) {
				$conferenceCategories->attach($categories[$categoryRecord->name]);
			}
			$conference->setCategories($conferenceCategories);

			$this->conferenceRepository->add($conference);
		}

		$this->response->setStatus(204);
	}
}

?>