<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Domain\Repository;

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
 * A repository for ContactRequests
 *
 * @package CaP
 * @subpackage Domain
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ContactRequestRepository extends \F3\FLOW3\Persistence\Repository {

	public function findBySenderAndReceiver(\F3\CaP\Domain\Model\Member $sender, \F3\CaP\Domain\Model\Member $receiver) {
		$query = $this->createQuery();
		return $query->matching(
			$query->logicalAnd(
				$query->equals('sender', $sender),
				$query->equals('receiver', $receiver)
			)
		)->execute();
	}

	public function findOpenByReceiver(\F3\CaP\Domain\Model\Member $receiver) {
		$query = $this->createQuery();
		return $query->matching(
			$query->logicalAnd(
				$query->equals('status', \F3\CaP\Domain\Model\ContactRequest::RCD_REQUESTED),
				$query->equals('receiver', $receiver)
			)
		)->execute();
	}
}
?>