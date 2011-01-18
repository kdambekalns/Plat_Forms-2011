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
