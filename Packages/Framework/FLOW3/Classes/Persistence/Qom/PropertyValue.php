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
 * Evaluates to the value (or values, if multi-valued) of a property.
 *
 * If, for a tuple, the selector node does not have a property named property,
 * the operand evaluates to null.
 *
 * The query is invalid if:
 *
 * selector is not the name of a selector in the query, or
 * property is not a syntactically valid property name.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class PropertyValue extends \F3\FLOW3\Persistence\Qom\DynamicOperand {

	/**
	 * @var string
	 */
	protected $selectorName;

	/**
	 * @var string
	 */
	protected $propertyName;

	/**
	 * Constructs this PropertyValue instance
	 *
	 * @param string $propertyName
	 * @param string $selectorName
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct($propertyName, $selectorName = '') {
		$this->propertyName = $propertyName;
		$this->selectorName = $selectorName;
	}

	/**
	 * Gets the name of the selector against which to evaluate this operand.
	 *
	 * @return string the selector name; non-null
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function getSelectorName() {
		return $this->selectorName;
	}

	/**
	 * Gets the name of the property.
	 *
	 * @return string the property name; non-null
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function getPropertyName() {
		return $this->propertyName;
	}

}

?>