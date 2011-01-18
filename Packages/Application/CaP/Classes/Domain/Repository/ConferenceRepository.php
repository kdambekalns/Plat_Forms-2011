<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Domain\Repository;

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
 * Contract for a repository
 *
 * @origin: M
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class ConferenceRepository extends \F3\FLOW3\Persistence\Repository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'startDate' => \F3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING,
		'name' => \F3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING,
	);

	/**
	 * Finds a conference by the given category
	 *
	 * @param \F3\CaP\Domain\Model\Category $category
	 * @return \F3\CaP\Domain\Model\Conference
	 */
	public function findByCategory(\F3\CaP\Domain\Model\Category $category) {
		$query = $this->createQuery();
		return $query->matching($query->contains('categories', $category))->execute();
	}

	/**
	 * Finds conferences that are current, i.e. end today or later.
	 *
	 * @return \F3\FLOW3\Persistence\QueryResultInterface The query result
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findCurrent() {
		$currentConferences = array();

			// running today
		$query = $this->createQuery();
		$currentConferences['now'] = $query->matching(
			$query->logicalAnd(
				$query->lessThanOrEqual('startDate', new \DateTime('today')),
				$query->lessThanOrEqual('endDate', new \DateTime('today'))
			)
		)->execute();

			// starting tomorrow
		$query = $this->createQuery();
		$currentConferences['day'] = $query->matching(
			$query->equals('startDate', new \DateTime('tomorrow'))
		)->execute();

			// starting within next week
		$query = $this->createQuery();
		$currentConferences['week'] = $query->matching(
			$query->logicalAnd(
				$query->greaterThan('startDate', new \DateTime('tomorrow')),
				$query->lessThanOrEqual('startDate', new \DateTime('today +1 week'))
			)
		)->execute();

			// starting after next week
		$query = $this->createQuery();
		$currentConferences['future'] = $query->matching(
			$query->greaterThan('startDate', new \DateTime('today +1 week'))
		)->execute();

		return $currentConferences;
	}

	/**
	 * Finds conferences that are current, i.e. end today or later.
	 *
	 * @param \F3\CaP\Domain\Model\Category $category
	 * @return \F3\FLOW3\Persistence\QueryResultInterface The query result
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findCurrentByCategory(\F3\CaP\Domain\Model\Category $category) {
		$currentConferences = array();

			// running today
		$query = $this->createQuery();
		$currentConferences['now'] = $query->matching(
			$query->logicalAnd(
				$query->lessThanOrEqual('startDate', new \DateTime('today')),
				$query->lessThanOrEqual('endDate', new \DateTime('today')),
				$query->equals('category', $category)
			)
		)->execute();

			// starting tomorrow
		$query = $this->createQuery();
		$currentConferences['day'] = $query->matching(
			$query->logicalAnd(
				$query->equals('startDate', new \DateTime('tomorrow')),
				$query->equals('category', $category)
			)
		)->execute();

			// starting within next week
		$query = $this->createQuery();
		$currentConferences['week'] = $query->matching(
			$query->logicalAnd(
				$query->greaterThan('startDate', new \DateTime('tomorrow')),
				$query->lessThanOrEqual('startDate', new \DateTime('today +1 week')),
				$query->equals('category', $category)
			)
		)->execute();

			// starting after next week
		$query = $this->createQuery();
		$currentConferences['future'] = $query->matching(
			$query->logicalAnd(
				$query->greaterThan('startDate', new \DateTime('today +1 week')),
				$query->equals('category', $category)
			)
		)->execute();

		return $currentConferences;
	}

	/**
	 * Searches for conferences matching the criteria, expected to be
	 * array(
	 *	'terms' => array('foo', 'bar'),
	 *	'categories' => array('science'),
	 *	'options' => array('withsub' => TRUE),
	 *	'from' => new /DateTime(),
	 *	'until' => new /DateTime(),
	 *	'region' => 50
	 *	)
	 * Unused keys should be omitted.
	 *
	 * @param array $criteria
	 * @return \F3\FLOW3\Persistence\QueryResultInterface The query result
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findByCriteria(array $criteria) {
		$criteria = array_merge(
			array(
				'terms' => array(),
				'categories' => array(),
				'options' => array(),
				'from' => NULL,
				'until' => NULL,
				'region' => NULL
				),
			$criteria
		);
		$query = $this->createQuery();
		$qomParts = array();

		$qomTerms = array();
		foreach ($criteria['terms'] as $term) {
			$qomTerms[] = $query->logicalOr(
				$query->like('name', '%' . $term . '%', TRUE),
				$query->like('description', '%' . $term . '%', TRUE)
			);
		}
		if (count($qomTerms) > 0) {
			$qomParts[] = $query->logicalAnd($qomTerms);
		}

		if ($criteria['from'] === NULL && $criteria['until'] === NULL) {
			$qomParts[] = $query->lessThanOrEqual('endDate', new \DateTime('today'));
		} else {
			if ($criteria['from'] !== NULL) {
				$qomParts[] = $query->lessThanOrEqual('endDate',$criteria['from']);
			}
			if ($criteria['until'] !== NULL) {
				$qomParts[] = $query->greaterThanOrEqual('startDate',$criteria['until']);
			}
		}

		// categories
		// SplObjectStorage<F3\CaP\Domain\Model\Category>

		// region
		// SplObjectStorage<F3\Party\Domain\Model\Address>

		return $query->matching($query->logicalAnd($qomParts))->execute();
	}

	/**
	 * Parse a query as defined by M26
	 *
	 * @param string $query
	 * @return array
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function parseUserQuery($userQuery) {
		$query = array(
			'terms' => array(),
			'categories' => array(),
			'options' => array(),
			'from' => NULL,
			'until' => NULL,
			'region' => NULL
		);

		$userQueryParts = explode(' ', $userQuery);
		foreach ($userQueryParts as $userQueryPart) {
			if (strpos($userQueryPart, ':') !== FALSE) {
				$userQueryPart = explode(':', $userQueryPart);
				switch ($userQueryPart[0]) {
					case 'cat':
						$query['categories'][] = $userQueryPart[1];
						break;
					case 'opt':
						$query['options'][$userQueryPart[1]] = TRUE;
						break;
					case 'from':
						$query['from'] = \F3\CaP\Utility\DateConverter::createDateFromString($userQueryPart[1]);
						break;
					case 'until':
						$query['until'] = \F3\CaP\Utility\DateConverter::createDateFromString($userQueryPart[1]);
						break;
					case 'reg':
						if (is_numeric($userQueryPart[1])) {
							$query['region'] = (integer) $userQueryPart[1];
						} elseif ($userQueryPart[1] === 'country') {
							$query['region'] = $userQueryPart[1];
						}
						break;
				}
			} elseif (!empty($userQueryPart)) {
				$query['terms'][] = $userQueryPart;
			}
		}

		return $query;
	}
}
