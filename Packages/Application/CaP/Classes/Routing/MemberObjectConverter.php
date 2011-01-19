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
 * An Object Converter for Member objects
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @origin: RM
 */
class MemberObjectConverter implements \F3\FLOW3\Property\ObjectConverterInterface {

	/**
	 * @inject
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * Returns a list of fully qualified class names of those classes which are supported
	 * by this property editor.
	 *
	 * @return array<string>
	 */
	public function getSupportedTypes() {
		return array('F3\CaP\Domain\Model\Member');
	}

	/**
	 * Converts the given username to a Member object
	 *
	 * @return mixed A member object or FALSE
	 */
	public function convertFrom($source) {
		if (!is_string($source)) {
			return FALSE;
		}
		$account = $this->accountRepository->findOneByAccountIdentifier(urldecode($source));
		if ($account === NULL) {
			return FALSE;
		}
		return $account->getParty();
	}
}
?>