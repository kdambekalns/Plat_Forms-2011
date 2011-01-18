<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Domain\Model;

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
 * A Category
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 * @origin: M
 */
class Category {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var \F3\CaP\Domain\Model\Category
	 */
	protected $parent;

	/**
	 *
	 * @param string $name Name of the category
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Get the Category's name
	 *
	 * @return string The Category's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the parent category
	 *
	 * @param \F3\CaP\Domain\Model\Category $parentCategory
	 * @return void
	 */
	public function setParent(\F3\CaP\Domain\Model\Category $parentCategory) {
		$this->parent = $parentCategory;
	}

	/**
	 * @return \F3\CaP\Domain\Model\Category
	 */
	public function getParent() {
		return $this->parent;
	}
}
?>