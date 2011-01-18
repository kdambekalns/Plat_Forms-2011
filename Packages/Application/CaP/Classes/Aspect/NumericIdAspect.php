<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Aspect;

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
 * An aspect which introduces numeric ids to models of this application
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @aspect
 * @origin: M
 */
class NumericIdAspect {

	/**
	 * @introduce F3\CaP\Aspect\NumericIdAwareInterface, classTaggedWith(entity)
	 */
	public $numericIdAwareInterface;

	/**
	 * @around classTaggedWith(entity) && method(.*->getNumericId())
	 * @param JoinPointInterface $joinPoint
	 * @return integer
	 */
	public function getIdAdvice(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		return hexdec(substr($joinPoint->getProxy()->FLOW3_Persistence_Entity_UUID, 0, 16));
	}

}

?>