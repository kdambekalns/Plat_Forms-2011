<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Routing;

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
 * A route part handler for Conference
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @origin: RM
 */
class ConferenceRoutePartHandler extends \F3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

	/**
	 * @inject
	 * @var \F3\CaP\Domain\Repository\ConferenceRepository
	 */
	protected $conferenceRepository;

	/**
	 * While matching, resolves the requested content
	 *
	 * @param string $value the complete path
	 * @return boolean
	 */
	protected function matchValue($value) {
		$conference = $this->conferenceRepository->findByUuid($value);
		if ($conference === NULL) {
			return FALSE;
		}

		$this->value = $conference;
		return TRUE;
	}

	/**
	 * Checks, whether given value can be resolved and if so, sets $this->value to the resolved value.
	 * If $value is empty, this method checks whether a default value exists.
	 *
	 * @param string $value value to resolve
	 * @return boolean TRUE if value could be resolved successfully, otherwise FALSE.
	 */
	protected function resolveValue($value) {
		if (!$value instanceof \F3\Cap\Domain\Model\Conference) {
			return FALSE;
		}
		$this->value = $value->FLOW3_Persistence_Entity_UUID;
		return TRUE;
	}

}
?>