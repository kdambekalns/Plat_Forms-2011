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
	 * @var string
	 */
	protected $venue;

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $locationByCoordinates;

	/**
	 * @var \SplObjectStorage
	 */
	protected $categories;

	/**
	 * @var \F3\CaP\Domain\Model\Member
	 */
	protected $creator;

	/**
	 * @var \SplObjectStorage<\F3\CaP\Domain\Model\Member>
	 */
	protected $attendees;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->categories = new \SplObjectStorage();
		$this->attendees = new \SplObjectStorage();
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

	/**
	 * @param array<\F3\CaP\Domain\Model\Category> $categories
	 */
	public function setCategories(array $categories) {
		$this->categories = new \SplObjectStorage($categories);
		foreach ($categories as $category) {
			$this->categories->attach($category);
		}
	}

	/**
	 * @return array <\F3\CaP\Domain\Model\Category>
	 */
	public function getCategories() {
		return (array)$this->categories;
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
	 * @param string $endDate
	 * @return void
	 */
	public function setEndDateAsString($endDate) {
		$this->endDate = \F3\CaP\Utility\DateConverter::createDateFromString($endDate);
	}

	/**
	 * @return string
	 */
	public function getEndDateAsString() {
		return $this->endDate->format('Y/m/d');
	}

	/**
	 * @param string $location
	 * @return void
	 */
	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @param string $venue
	 * @return void
	 */
	public function setVenue($venue) {
		$this->venue = $venue;
	}

	/**
	 * @return string
	 */
	public function getVenue() {
		return $this->venue;
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

	/**
	 * @param string $startDate
	 * @return void
	 */
	public function setStartDateAsString($startDate) {
		$this->startDate = \F3\CaP\Utility\DateConverter::createDateFromString($startDate);
	}

	/**
	 * @return string
	 */
	public function getStartDateAsString() {
		return $this->startDate->format('Y/m/d');
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $creator
	 * @return void
	 */
	public function setCreator(\F3\CaP\Domain\Model\Member $creator) {
		$this->creator = $creator;
	}

	/**
	 * Returns the creator of this conference
	 *
	 * @return \F3\CaP\Domain\Model\Member
	 */
	public function getCreator() {
		return $this->creator;
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $attendee
	 * @return void
	 */
	public function addAttendee(\F3\CaP\Domain\Model\Member $attendee) {
		$this->attendees->attach($attendee);
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $attendee
	 * @return void
	 */
	public function removeAttendee(\F3\CaP\Domain\Model\Member $attendee) {
		$this->attendees->detach($attendee);
	}

	/**
	 * @return \SplObjectStorage<\F3\CaP\Domain\Model\Member>
	 * @return void
	 */
	public function getAttendees() {
		return $this->attendees;
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $member
	 * @return boolean TRUE, if the given member attends to this conference
	 * @return void
	 */
	public function isAttendee(\F3\CaP\Domain\Model\Member $member) {
		return $this->attendees->contains($member);
	}

	/**
	 * @param \F3\CaP\Domain\Model\Member $member
	 * @return boolean TRUE, if the given member is the creator of this conference
	 * @return void
	 */
	public function isCreator(\F3\CaP\Domain\Model\Member $member) {
		return $this->creator === $member;
	}

}
?>