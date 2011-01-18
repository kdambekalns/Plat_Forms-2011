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
 * A Conference
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 */
class Conference {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var \DateTime
	 */
	protected $startDate;

	/**
	 * @var \DateTime
	 */
	protected $endDate;

	/**
	 * @var \F3\Party\Domain\Model\Address
	 */
	protected $address;

	/**
	 * @var string
	 */
	protected $locationByCoordinates;

	/**
	 * @var \SplObjectStorage
	 */
	protected $categories;

	/**
	 *
	 */
	public function __construct() {
		$this->categories = new \SplObjectStorage();
	}

	/**
	 * Get the Conference's name
	 *
	 * @return string The Conference's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets this Conference's name
	 *
	 * @param string $name The Conference's name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	public function setAddress($address) {
		$this->address = $address;
	}

	public function getAddress() {
		return $this->address;
	}

	public function setCategories($categories) {
		$this->categories = $categories;
	}

	public function getCategories() {
		return $this->categories;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setEndDate(\DateTime $endDate) {
		$this->endDate = $endDate;
	}

	public function getEndDate() {
		return $this->endDate;
	}

	public function setLocationByCoordinates($locationByCoordinates) {
		$this->locationByCoordinates = $locationByCoordinates;
	}

	public function getLocationByCoordinates() {
		return $this->locationByCoordinates;
	}

	public function setStartDate(\DateTime $startDate) {
		$this->startDate = $startDate;
	}

	public function getStartDate() {
		return $this->startDate;
	}


}
?>