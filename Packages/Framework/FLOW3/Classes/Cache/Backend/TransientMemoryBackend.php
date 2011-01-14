<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Cache\Backend;

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
 * A caching backend which stores cache entries during one script run.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class TransientMemoryBackend extends \F3\FLOW3\Cache\Backend\AbstractBackend {

	/**
	 * @var array
	 */
	protected $entries = array();

	/**
	 * @var array
	 */
	protected $tagsAndEntries = array();

	/**
	 * Saves data in the cache.
	 *
	 * @param string $entryIdentifier An identifier for this specific cache entry
	 * @param string $data The data to be stored
	 * @param array $tags Tags to associate with this cache entry
	 * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
	 * @return void
	 * @throws \F3\FLOW3\Cache\Exception if no cache frontend has been set.
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		if (!$this->cache instanceof \F3\FLOW3\Cache\Frontend\FrontendInterface) throw new \F3\FLOW3\Cache\Exception('No cache frontend has been set yet via setCache().', 1238244992);
		if (!is_string($data)) throw new \F3\FLOW3\Cache\Exception\InvalidDataException('The specified data is of type "' . gettype($data) . '" but a string is expected.', 1238244993);
		$this->entries[$entryIdentifier] = $data;
		foreach ($tags as $tag) {
			$this->tagsAndEntries[$tag][$entryIdentifier] = TRUE;
		}
	}

	/**
	 * Loads data from the cache.
	 *
	 * @param string $entryIdentifier An identifier which describes the cache entry to load
	 * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function get($entryIdentifier) {
		return (isset($this->entries[$entryIdentifier])) ? $this->entries[$entryIdentifier] : FALSE;
	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function has($entryIdentifier) {
		return isset($this->entries[$entryIdentifier]);
	}

	/**
	 * Removes all cache entries matching the specified identifier.
	 *
	 * @param string $entryIdentifier Specifies the cache entry to remove
	 * @return boolean TRUE if the entry could be removed or FALSE if no entry was found
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function remove($entryIdentifier) {
		if (isset($this->entries[$entryIdentifier]))  {
			unset($this->entries[$entryIdentifier]);
			foreach (array_keys($this->tagsAndEntries) as $tag) {
				if (isset($this->tagsAndEntries[$tag][$entryIdentifier])) {
					unset ($this->tagsAndEntries[$tag][$entryIdentifier]);
				}
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Finds and returns all cache entry identifiers which are tagged by the
	 * specified tag.
	 *
	 * @param string $tag The tag to search for
	 * @return array An array with identifiers of all matching entries. An empty array if no entries matched
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function findIdentifiersByTag($tag) {
		if (isset($this->tagsAndEntries[$tag])) {
			return array_keys($this->tagsAndEntries[$tag]);
		} else {
			return array();
		}
	}

	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function flush() {
		$this->entries = array();
		$this->tagsAndEntries = array();
	}

	/**
	 * Removes all cache entries of this cache which are tagged by the specified tag.
	 *
	 * @param string $tag The tag the entries must have
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function flushByTag($tag) {
		$identifiers = $this->findIdentifiersByTag($tag);
		foreach ($identifiers as $identifier) {
			$this->remove($identifier);
		}
	}

	/**
	 * Does nothing
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function collectGarbage() {
	}
}
?>