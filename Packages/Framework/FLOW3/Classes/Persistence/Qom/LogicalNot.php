<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Persistence\Qom;

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
 * Performs a logical negation of another constraint.
 *
 * To satisfy the Not constraint, the tuple must not satisfy $constraint.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class LogicalNot extends \F3\FLOW3\Persistence\Qom\Constraint {

	/**
	 * @var \F3\FLOW3\Persistence\Qom\Constraint
	 */
	protected $constraint;

	/**
	 *
	 * @param \F3\FLOW3\Persistence\Qom\Constraint $constraint
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct(\F3\FLOW3\Persistence\Qom\Constraint $constraint) {
		$this->constraint = $constraint;
	}

	/**
	 * Gets the constraint negated by this Not constraint.
	 *
	 * @return \F3\FLOW3\Persistence\Qom\Constraint the constraint; non-null
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function getConstraint() {
		return $this->constraint;
	}

}
?>