<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\Authorization;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
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
 * Contract for an access decision voter.
 *
 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface AccessDecisionVoterInterface {

	const
		VOTE_GRANT = 1,
		VOTE_ABSTAIN = 2,
		VOTE_DENY = 3;

	/**
	 * Votes if access should be granted for the given object in the current security context
	 *
	 * @param \F3\FLOW3\Security\Context $securityContext The current securit context
	 * @param \F3\FLOW3\AOP\JoinPointInterface $joinPoint The joinpoint to vote for
	 * @return integer One of: VOTE_GRANT, VOTE_ABSTAIN, VOTE_DENY
	 * @throws \F3\FLOW3\Security\Exception\AccessDeniedException If access is not granted
	 */
	public function voteForJoinPoint(\F3\FLOW3\Security\Context $securityContext, \F3\FLOW3\AOP\JoinPointInterface $joinPoint);

	/**
	 * Votes if access should be granted for the given resource in the current security context
	 *
	 * @param \F3\FLOW3\Security\Context $securityContext The current securit context
	 * @param string $resource The resource to vote for
	 * @return integer One of: VOTE_GRANT, VOTE_ABSTAIN, VOTE_DENY
	 * @throws \F3\FLOW3\Security\Exception\AccessDeniedException If access is not granted
	 */
	public function voteForResource(\F3\FLOW3\Security\Context $securityContext, $resource);
}

?>