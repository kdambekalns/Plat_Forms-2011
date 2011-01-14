<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Persistence;

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
 * Contract for a repository
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Robert Lemke <robert@typo3.org>
 * @api
 */
interface RepositoryInterface {

	/**
	 * Adds an object to this repository.
	 *
	 * @param object $object The object to add
	 * @return void
	 * @api
	 */
	public function add($object);

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 * @return void
	 * @api
	 */
	public function remove($object);

	/**
	 * Returns all objects of this repository add()ed but not yet persisted to
	 * the storage layer.
	 *
	 * @return array An array of objects
	 */
	public function getAddedObjects();

	/**
	 * Returns an array with objects remove()d from the repository that
	 * had been persisted to the storage layer before.
	 *
	 * @return array
	 */
	public function getRemovedObjects();

	/**
	 * Returns all objects of this repository.
	 *
	 * @return array An array of objects, empty if no objects found
	 * @api
	 */
	public function findAll();

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param string $uuid The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByUuid($uuid);

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return \F3\FLOW3\Persistence\QueryInterface
	 * @api
	 */
	public function createQuery();

	/**
	 * Counts all objects of this repository
	 *
	 * @return integer
	 * @api
	 */
	public function countAll();

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll();

	/**
	 * Sets the property names to order results by. Expected like this:
	 * array(
	 *  'foo' => \F3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING,
	 *  'bar' => \F3\FLOW3\Persistence\QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $defaultOrderings The property names to order by by default
	 * @return void
	 * @api
	 */
	public function setDefaultOrderings(array $defaultOrderings);

	/**
	 * Replaces an object by another after checking that existing and new
	 * objects have the right types
	 *
	 * @param object $existingObject The existing object
	 * @param object $newObject The new object
	 * @api
	 */
	public function replace($existingObject, $newObject);

	/**
	 * Replaces an existing object with the same identifier by the given object
	 * after checking the type of the object fits to the repositories type
	 *
	 * @param object $modifiedObject The modified object
	 * @api
	 */
	public function update($modifiedObject);

}
?>