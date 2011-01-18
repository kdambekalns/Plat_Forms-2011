<?php
declare(ENCODING = 'utf-8');
namespace F3\Party\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Party".                      *
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
 * An address
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @entity
 * @origin RM
 */
class Address {

	/**
	 * Country details
	 * @var string
	 */
	protected $country;

	/**
	 * Details of Locality which is a named densely populated area  (a place) such as town, village, suburb, etc. A locality composes of many individual addresses.  Many localities exist in an administrative area or a sub adminisrative area. A locality can also have sub localities. For example, a municipality locality can have many villages associated with it which are sub localities. Example: Tamil Nadu State, Erode District, Bhavani Taluk, Paruvachi Village is a valid address in India. Tamil Nadu is the Administrative Area, Erode is the sub admin area, Bhavani is the locality, and Paruvachi is the sub locality
	 * @var string
	 */
	protected $locality;

	/**
	 * Details of the Access route along which buildings/lot/land are located, such as street, road, channel, crescent, avenue, etc. This also includes canals/banks on which houses/boat houses are located where people live
	 * @var string
	 */
	protected $thoroughfare;

	/**
	 * A container for a single free text or structured postcode. Note that not all countries have post codes
	 * @var string
	 */
	protected $postCode;

	/**
	 * The location by coordinates
	 * @var string
	 */
	protected $locationByCoordinates;

	/**
	 * Setter for country
	 *
	 * @param string $country Country details
	 * @return void
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * Getter for country
	 *
	 * @return string Country details
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Setter for locality
	 *
	 * @param string $locality Details of Locality which is a named densely populated area  (a place) such as town, village, suburb, etc. A locality composes of many individual addresses.  Many localities exist in an administrative area or a sub adminisrative area. A locality can also have sub localities. For example, a municipality locality can have many villages associated with it which are sub localities. Example: Tamil Nadu State, Erode District, Bhavani Taluk, Paruvachi Village is a valid address in India. Tamil Nadu is the Administrative Area, Erode is the sub admin area, Bhavani is the locality, and Paruvachi is the sub locality
	 * @return void
	 */
	public function setLocality($locality) {
		$this->locality = $locality;
	}

	/**
	 * Getter for locality
	 *
	 * @return string Details of Locality which is a named densely populated area  (a place) such as town, village, suburb, etc. A locality composes of many individual addresses.  Many localities exist in an administrative area or a sub adminisrative area. A locality can also have sub localities. For example, a municipality locality can have many villages associated with it which are sub localities. Example: Tamil Nadu State, Erode District, Bhavani Taluk, Paruvachi Village is a valid address in India. Tamil Nadu is the Administrative Area, Erode is the sub admin area, Bhavani is the locality, and Paruvachi is the sub locality
	 */
	public function getLocality() {
		return $this->locality;
	}

	/**
	 * Setter for thoroughfare
	 *
	 * @param string $thoroughfare Details of the Access route along which buildings/lot/land are located, such as street, road, channel, crescent, avenue, etc. This also includes canals/banks on which houses/boat houses are located where people live
	 * @return void
	 */
	public function setThoroughfare($thoroughfare) {
		$this->thoroughfare = $thoroughfare;
	}

	/**
	 * Getter for thoroughfare
	 *
	 * @return string Details of the Access route along which buildings/lot/land are located, such as street, road, channel, crescent, avenue, etc. This also includes canals/banks on which houses/boat houses are located where people live
	 */
	public function getThoroughfare() {
		return $this->thoroughfare;
	}

	/**
	 * Setter for postCode
	 *
	 * @param string $postCode A container for a single free text or structured postcode. Note that not all countries have post codes
	 * @return void
	 */
	public function setPostCode($postCode) {
		$this->postCode = $postCode;
	}

	/**
	 * Getter for postCode
	 *
	 * @return string A container for a single free text or structured postcode. Note that not all countries have post codes
	 */
	public function getPostCode() {
		return $this->postCode;
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
}

?>