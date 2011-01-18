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
 * @origin: M
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
	 * @var \SplObjectStorage<F3\CaP\Domain\Model\Category>
	 */
	protected $categories;

	/**
	 * @param \F3\Party\Domain\Model\Address $address
	 * @return void
	 */
	public function setAddress(\F3\Party\Domain\Model\Address $address) {
		$this->address = $address;
	}

	/**
	 * @return
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @param SplObjectStorage $categories
	 * @return void
	 */
	public function setCategories(\SplObjectStorage $categories) {
		$this->categories = $categories;
	}

	/**
	 * @return
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param \DateTime $endDate
	 * @return void
	 */
	public function setEndDate(\DateTime $endDate) {
		$this->endDate = $endDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getEndDate() {
		return $this->endDate;
	}

	/**
	 * @param string $locationByCoordinates
	 * @return void
	 */
	public function setLocationByCoordinates($locationByCoordinates) {
		$this->locationByCoordinates = $locationByCoordinates;
	}

	/**
	 * @return string
	 */
	public function getLocationByCoordinates() {
		return $this->locationByCoordinates;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param \DateTime $startDate
	 * @return void
	 */
	public function setStartDate(\DateTime $startDate) {
		$this->startDate = $startDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getStartDate() {
		return $this->startDate;
	}
}
?>