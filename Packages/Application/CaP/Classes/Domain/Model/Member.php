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
class Member extends \F3\Party\Domain\Model\Person {

	/**
	 * The contacts
	 *
	 * @var array
	 */
	protected $contacts;

	/**
	 * Get the Member's contacts
	 *
	 * @return \SplObjectStorage The Member's contacts
	 */
	public function getContacts() {
		return $this->contacts;
	}

	/**
	 * Sets this Member's contacts
	 *
	 * @param  $contact
	 * @return void
	 */
	public function addContact($contact) {
		$this->contacts->attach($contact);
	}

	/**
	 * Returns the username of the website account
	 * 
	 * @return string The username for the web account of this member
	 */
	public function getWebUsername() {
		foreach ($this->accounts as $account) {
			if ($account->getAuthenticationProviderName() === 'DefaultProvider') {
				return $account->getAccountIdentifier();
			}
		}
	}

}
?>