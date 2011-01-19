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
 * A repository for Members
 *
 * @package CaP
 * @subpackage Domain
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MemberRepository extends \F3\FLOW3\Persistence\Repository {


	/**
	 * @inject
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \F3\FLOW3\Security\Account
	 */
	protected $account;

	/**
	 * Initializes the onbject
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeObject() {
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				$this->account = $token->getAccount();
			}
		}
	}

	/**
	 * array(2)
	 * 'terms' (6) => '' (0)
	 * 'location' (8) => array(2)
	 *   'locality' (8) => '1' (1)
	 *   'country' (7) => '' (0)
	 *
	 * @param array $filter
	 * @return array<\F3\CaP\Domain\Model\Member>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findWithFilter(array $filter) {
		$query = $this->createQuery();
		$qomParts = array();

		if (count($filter) > 1) {
			$terms = explode(' ', $filter['terms']);
			if (count($terms) > 0) {
				$qomTerms = array();
				foreach ($terms as $term) {
					$qomTerms[] = $query->like('fullName', '%' . $term . '%', TRUE);
				}
				$qomParts[] = $query->logicalAnd($qomTerms);
			}
			if ($filter['location']['locality']) {
				$qomParts[] = $query->equals('town', $this->account->getParty()->getTown());
			}
			if ($filter['location']['country']) {
				$qomParts[] = $query->equals('country', $this->account->getParty()->getCountry());
			}

			if (count($qomParts) > 0) {
				$members = $query->matching($query->logicalAnd($qomParts))->execute()->toArray();
				usort($members, function($a, $b) {
					$nameA = $a->getUsername();
					$nameB = $b->getUsername();
					return ($nameA < $nameB) ? -1 : 1;
				});
				return $members;
			} else {
				return $this->findAll();
			}
		} else {
			return $this->findAll();
		}

	}
}
?>