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
 * A contact request
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 * @origin M
 */
class ContactRequest {

	const
		RCD_REQUESTED = 1,
		RCD_DECLINED = 2,
		IN_CONTACT = 3;

	/**
	 * The sender of this request
	 * @var \F3\CaP\Domain\Model\Member
	 */
	protected $sender;

	/**
	 * The receiver of this request
	 * @var \F3\CaP\Domain\Model\Member
	 */
	protected $receiver;

	/**
	 * @var integer
	 */
	protected $status = self::RCD_REQUESTED;

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @param \F3\CaP\Domain\Model\Member $receiver
	 * @return void
	 */
	public function setReceiver(\F3\CaP\Domain\Model\Member $receiver) {
		$this->receiver = $receiver;
	}

	/**
	 * @return \F3\CaP\Domain\Model\Member
	 */
	public function getReceiver() {
		return $this->receiver;
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $sender
	 * @return void
	 */
	public function setSender(\F3\CaP\Domain\Model\Member $sender) {
		$this->sender = $sender;
	}

	/**
	 * @return \F3\CaP\Domain\Model\Member
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @param integer $status
	 * @return void
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return void
	 */
	protected function acceptRequest() {
		$currentAccount = NULL;
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				$currentAccount = $token->getAccount();
			}
		}
		if ($currentAccount === $this->receiver) {
			$this->receiver->addContact($this->sender);
			$this->sender->addContact($this->receiver);
		}
	}

	/**
	 * @return void
	 */
	protected function declineRequest() {
		$currentAccount = NULL;
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				$currentAccount = $token->getAccount();
			}
		}
		if ($currentAccount === $this->receiver) {
			$this->status = self::RCD_DECLINED;
		}
	}
}
?>