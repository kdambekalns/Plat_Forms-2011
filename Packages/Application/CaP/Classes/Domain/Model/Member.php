<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Domain\Model;

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
 * A Member
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 * @origin: M
 */
class Member implements \F3\Party\Domain\Model\PartyInterface  {

	/**
	 * @var \F3\CaP\Domain\Repository\ContactRequestRepository
	 * @inject
	 */
	protected $contactRequestRepository;

	/**
	 * The contacts
	 *
	 * @var \SplObjectStorage<\F3\Party\Domain\Model\Member>
	 */
	protected $contacts;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $firstName;

	/**
	 * @var string
	 */
	protected $middleName;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $lastName;

	/**
	 * @var string
	 */
	protected $fullName;

	/**
	 * @var \SplObjectStorage<\F3\Party\Domain\Model\ElectronicAddress>
	 */
	protected $electronicAddresses;

	/**
	 * @var \F3\Party\Domain\Model\ElectronicAddress
	 */
	protected $primaryElectronicAddress;

	/**
	 * @var \SplObjectStorage<\F3\FLOW3\Security\Account>
	 */
	protected $accounts;

	/**
	 * @var string
	 */
	protected $street;

	/**
	 * @var string
	 */
	protected $town;

	/**
	 * @var string
	 */
	protected $country;

	/**
	 * @var string
	 */
	protected $locationByCoordinates;

	/**
	 * Constructs this Person
	 *
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct() {
		$this->electronicAddresses = new \SplObjectStorage();
		$this->accounts = new \SplObjectStorage();
	}

	/**
	 * Adds the given electronic address to this person.
	 *
	 * @param \F3\Party\Domain\Model\ElectronicAddress $electronicAddress The electronic address
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addElectronicAddress(\F3\Party\Domain\Model\ElectronicAddress $electronicAddress) {
		$this->electronicAddresses->attach($electronicAddress);
	}

	/**
	 * Removes the given electronic address from this person.
	 *
	 * @param \F3\Party\Domain\Model\ElectronicAddress $electronicAddress The electronic address
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function removeElectronicAddress(\F3\Party\Domain\Model\ElectronicAddress $electronicAddress) {
		$this->electronicAddresses->detach($electronicAddress);
		if ($electronicAddress === $this->primaryElectronicAddress) {
			unset($this->primaryElectronicAddress);
		}
	}

	/**
	 * Returns all known electronic addresses of this person.
	 *
	 * @return \SplObjectStorage<\F3\Party\Domain\Model\ElectronicAddress>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getElectronicAddresses() {
		return clone $this->electronicAddresses;
	}

	/**
	 * Sets (and adds if necessary) the primary electronic address of this person.
	 *
	 * @param \F3\Party\Domain\Model\ElectronicAddress $electronicAddress The electronic address
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setPrimaryElectronicAddress(\F3\Party\Domain\Model\ElectronicAddress $electronicAddress) {
		$this->primaryElectronicAddress = $electronicAddress;
		$this->electronicAddresses->attach($electronicAddress);
	}

	/**
	 * Returns the primary electronic address, if one has been defined.
	 *
	 * @return \F3\Party\Domain\Model\ElectronicAddress The primary electronic address or NULL
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPrimaryElectronicAddress() {
		return $this->primaryElectronicAddress;
	}

	/**
	 * Assigns the given account to this party. Note: The internal reference of the account is
	 * set to this party.
	 *
	 * @return F3\FLOW3\Security\Account $account The account
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function addAccount(\F3\FLOW3\Security\Account $account) {
		$this->accounts->attach($account);
		$account->setParty($this);
	}

	/**
	 * Remove an account from this party
	 *
	 * @param F3\FLOW3\Security\Account $account The account to remove
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function removeAccount(\F3\FLOW3\Security\Account $account) {
		$this->accounts->detach($account);
	}

	/**
	 * Returns the accounts of this party
	 *
	 * @return SplObjectStorage All assigned F3\FLOW3\Security\Account objects
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAccounts() {
		return $this->accounts;
	}

	/**
	 * Get the Member's contacts
	 *
	 * @return \SplObjectStorage The Member's contacts
	 */
	public function getContacts() {
		return $this->contacts;
	}

	/**
	 * Adds a contact to this member
	 *
	 * @param \F3\Party\Domain\Model\Member $contact
	 * @return void
	 */
	public function addContact($contact) {
		$this->contacts->attach($contact);
	}

	/**
	 * Removes a contact from this member
	 *
	 * @param \F3\Party\Domain\Model\Member $contact
	 * @return void
	 */
	public function removeContact($contact) {
		$this->contacts->detach($contact);
	}

	/**
	 * Returns the username of the website account
	 *
	 * @return string The username for the web account of this member
	 */
	public function getUsername() {
		foreach ($this->accounts as $account) {
			if ($account->getAuthenticationProviderName() === 'DefaultProvider') {
				return $account->getAccountIdentifier();
			}
		}
	}

	/**
	 * @param string $country
	 * @return void
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * @param string $firstName
	 * @return void
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
		$this->updateFullName();
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param string $middleName
	 * @return void
	 */
	public function setMiddleName($middleName) {
		$this->middleName = $middleName;
		$this->updateFullName();
	}

	/**
	 * @return string
	 */
	public function getMiddleName() {
		return $this->middleName;
	}

	/**
	 * @param string $lastName
	 * @return void
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
		$this->updateFullName();
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @return string
	 */
	public function getFullName() {
		return $this->fullName;
	}

	/**
	 * @param string $street
	 * @return void
	 */
	public function setStreet($street) {
		$this->street = $street;
	}

	/**
	 * @return string
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * @param string $town
	 * @return void
	 */
	public function setTown($town) {
		$this->town = $town;
	}

	/**
	 * @return string
	 */
	public function getTown() {
		return $this->town;
	}

	/**
	 * @param string $locationByCoordinates
	 * @return void
	 */
	public function setLocationByCoordinates($locationByCoordinates) {
		$this->locationByCoordinates = $locationByCoordinates;
	}

	/**
	 * @return string
	 */
	public function getLocationByCoordinates() {
		return $this->locationByCoordinates;
	}

	/**
	 * @return void
	 */
	protected function updateFullName() {
		$nameParts = array(
			$this->firstName,
			$this->middleName,
			$this->lastName,
		);
		$nameParts = array_map('trim', $nameParts);
		$filledNameParts = array();
		foreach($nameParts as $namePart) {
			if($namePart !== '') {
				$filledNameParts[] = $namePart;
			}
		}
		$this->fullName = implode(' ', $filledNameParts);
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $member
	 * @return void
	 */
	public function isContactOf(\F3\CaP\Domain\Model\Member $member) {
		return $this->contacts->contains($member);
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $member
	 * @return void
	 */
	public function hasContactRequestSentTo(\F3\CaP\Domain\Model\Member $member) {
		return $this->contactRequestRepository->findBySenderAndReceiver($this, $member)->count() > 0;
	}
}
?>