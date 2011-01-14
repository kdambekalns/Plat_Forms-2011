<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Cache\Frontend;

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
 * Contract for a Cache (frontend)
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @author Robert Lemke <robert@typo3.org>
 */
interface FrontendInterface {

	/**
	 * "Magic" tag for class-related entries
	 */
	const TAG_CLASS = '%CLASS%';

	/**
	 * "Magic" tag for package-related entries
	 */
	const TAG_PACKAGE = '%PACKAGE%';

	/**
	 * Pattern an entry identifer must match.
	 */
	const PATTERN_ENTRYIDENTIFIER = '/^[a-zA-Z0-9_%\-&]{1,250}$/';

	/**
	 * Pattern a tag must match.
	 */
	const PATTERN_TAG = '/^[a-zA-Z0-9_%\-&]{1,250}$/';

	/**
	 * Returns this cache's identifier
	 *
	 * @return string The identifier for this cache
	 * @api
	 */
	public function getIdentifier();

	/**
	 * Returns the backend used by this cache
	 *
	 * @return \F3\FLOW3\Cache\Backend\BackendInterface The backend used by this cache
	 * @api
	 */
	public function getBackend();

	/**
	 * Saves data in the cache.
	 *
	 * @param string $entryIdentifier Something which identifies the data - depends on concrete cache
	 * @param mixed $data The data to cache - also depends on the concrete cache implementation
	 * @param array $tags Tags to associate with this cache entry
	 * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
	 * @return void
	 * @api
	 */
	public function set($entryIdentifier, $data, $tags = array(), $lifetime = NULL);

	/**
	 * Finds and returns data from the cache.
	 *
	 * @param string $entryIdentifier Something which identifies the cache entry - depends on concrete cache
	 * @return mixed
	 * @api
	 */
	public function get($entryIdentifier);

	/**
	 * Finds and returns all cache entries which are tagged by the specified tag.
	 *
	 * @param string $tag The tag to search for
	 * @return array An array with the content of all matching entries. An empty array if no entries matched
	 * @api
	 */
	public function getByTag($tag);

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 * @api
	 */
	public function has($entryIdentifier);

	/**
	 * Removes the given cache entry from the cache.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 */
	public function remove($entryIdentifier);

	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 */
	function flush();

	/**
	 * Removes all cache entries of this cache which are tagged by the specified tag.
	 *
	 * @param string $tag The tag the entries must have
	 * @return void
	 * @api
	 */
	public function flushByTag($tag);

	/**
	 * Does garbage collection
	 *
	 * @return void
	 * @api
	 */
	public function collectGarbage();

	/**
	 * Checks the validity of an entry identifier. Returns true if it's valid.
	 *
	 * @param string $identifier An identifier to be checked for validity
	 * @return boolean
	 * @api
	 */
	public function isValidEntryIdentifier($identifier);

	/**
	 * Checks the validity of a tag. Returns true if it's valid.
	 *
	 * @param string $tag A tag to be checked for validity
	 * @return boolean
	 * @api
	 */
	public function isValidTag($tag);

}
?>