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
 * A route part handler for Member
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @origin: RM
 */
class MemberRoutePartHandler extends \F3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * While matching, resolves the requested content
	 *
	 * @param string $value the complete path
	 * @return boolean
	 */
	protected function matchValue($value) {
		$account = $this->accountRepository->findOneByAccountIdentifier($value);
		if ($account === NULL) {
			return FALSE;
		}

		$this->value = $account->getParty();
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
		if (!$value instanceof \F3\Cap\Domain\Model\Member) {
			return FALSE;
		}
		$this->value = $value->getUsername();
		return TRUE;
	}

}
?>