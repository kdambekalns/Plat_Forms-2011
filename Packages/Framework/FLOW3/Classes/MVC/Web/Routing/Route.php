<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\MVC\Web\Routing;

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
 * Implementation of a standard route
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class Route {

	const ROUTEPART_TYPE_STATIC = 'static';
	const ROUTEPART_TYPE_DYNAMIC = 'dynamic';
	const PATTERN_EXTRACTROUTEPARTS = '/(?P<optionalStart>\(?)(?P<dynamic>{?)(?P<content>@?[^}{\(\)]+)}?(?P<optionalEnd>\)?)/';

	/**
	 * Route name
	 *
	 * @var string
	 */
	protected $name = NULL;

	/**
	 * Default values
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * URI Pattern of this route
	 *
	 * @var string
	 */
	protected $uriPattern = NULL;

 	/**
	 * Specifies whether Route Parts of this Route should be converted to lower case when resolved.
	 *
	 * @var boolean
	 */
	protected $lowerCase = FALSE;

	/**
	 * Contains the routing results (indexed by "package", "controller" and
	 * "action") after a successful call of matches()
	 *
	 * @var array
	 */
	protected $matchResults = array();

	/**
	 * Contains the matching uri (excluding protocol and host) after a
	 * successful call of resolves()
	 *
	 * @var string
	 */
	protected $matchingUri;

	/**
	 * Contains associative array of Route Part options
	 * (key: Route Part name, value: array of Route Part options)
	 *
	 * @var array
	 */
	protected $routePartsConfiguration = array();

	/**
	 * Indicates whether this route is parsed.
	 * For better performance, routes are only parsed if needed.
	 *
	 * @var boolean
	 */
	protected $isParsed = FALSE;

	/**
	 * Container for Route Parts.
	 *
	 * @var array
	 */
	protected $routeParts = array();

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Constructor
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function __construct(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injects the Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <rober@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Sets Route name.
	 *
	 * @param string $name The Route name
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the name of this Route.
	 *
	 * @return string Route name.
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets default values for this Route.
	 * This array is merged with the actual matchResults when match() is called.
	 *
	 * @param array $defaults
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setDefaults(array $defaults) {
		$this->defaults = $defaults;
	}

	/**
	 * Returns default values for this Route.
	 *
	 * @return array Route defaults
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getDefaults() {
		return $this->defaults;
	}

	/**
	 * Sets the URI pattern this route should match with
	 *
	 * @param string $uriPattern
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setUriPattern($uriPattern) {
		if (!is_string($uriPattern)) throw new \InvalidArgumentException('URI Pattern must be of type string, ' . gettype($uriPattern) . ' given.', 1223499724);
		$this->uriPattern = $uriPattern;
		$this->isParsed = FALSE;
	}

	/**
	 * Returns the URI pattern this route should match with
	 *
	 * @return string the URI pattern
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getUriPattern() {
		return $this->uriPattern;
	}

 	/**
	 * Specifies whether Route parts of this route should be converted to lower case when resolved.
	 * This setting can be overwritten for all dynamic Route parts.
	 *
	 * @param boolean $lowerCase TRUE: Route parts are converted to lower case by default. FALSE: Route parts are not altered.
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setLowerCase($lowerCase) {
		$this->lowerCase = $lowerCase;
	}

	/**
	 * Getter for $this->lowerCase.
	 *
	 * @return boolean TRUE if this Route part will be converted to lower case, otherwise FALSE.
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see setLowerCase()
	 */
	public function isLowerCase() {
		return $this->lowerCase;
	}

	/**
	 * By default all Dynamic Route Parts are resolved by
	 * \F3\FLOW3\MVC\Web\Routing\DynamicRoutePart.
	 * But you can specify different classes to handle particular Route Parts.
	 *
	 * Note: Route Part handlers must implement
	 * \F3\FLOW3\MVC\Web\Routing\DynamicRoutePartInterface.
	 *
	 * Usage: setRoutePartsConfiguration(array('@controller' =>
	 *            array('handler' => 'F3\Package\Subpackage\MyRoutePartHandler')));
	 *
	 * @param array $routePartsConfiguration Route Parts configuration options
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setRoutePartsConfiguration(array $routePartsConfiguration) {
		$this->routePartsConfiguration = $routePartsConfiguration;
	}

	/**
	 *
	 * @return array $routePartsConfiguration
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getRoutePartsConfiguration() {
		return $this->routePartsConfiguration;
	}

	/**
	 * Returns an array with the Route match results.
	 *
	 * @return array An array of Route Parts and their values for further handling by the Router
	 * @see \F3\FLOW3\MVC\Web\Routing\Router
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getMatchResults() {
		return $this->matchResults;
	}

	/**
	 * Returns the uri which corresponds to this Route.
	 *
	 * @return string A string containing the corresponding uri (excluding protocol and host)
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getMatchingUri() {
		return $this->matchingUri;
	}

	/**
	 * Checks whether $routePath corresponds to this Route.
	 * If all Route Parts match successfully TRUE is returned and
	 * $this->matchResults contains an array combining Route default values and
	 * calculated matchResults from the individual Route Parts.
	 *
	 * @param string $routePath the route path without protocol, host and query string
	 * @return boolean TRUE if this Route corresponds to the given $routePath, otherwise FALSE
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see getMatchResults()
	 */
	public function matches($routePath) {
		$this->matchResults = NULL;
		if ($routePath === NULL) {
			return FALSE;
		}
		if ($this->uriPattern === NULL) {
			return FALSE;
		}
		if (!$this->isParsed) {
			$this->parse();
		}
		$matchResults = array();

		$routePath = trim($routePath, '/');
		$skipOptionalParts = FALSE;
		$optionalPartCount = 0;
		foreach ($this->routeParts as $routePart) {
			if ($routePart->isOptional()) {
				$optionalPartCount++;
				if ($skipOptionalParts) {
					if ($routePart->getDefaultValue() === NULL) {
						return FALSE;
					}
					continue;
				}
			} else {
				$optionalPartCount = 0;
				$skipOptionalParts = FALSE;
			}
			if ($routePart->match($routePath) !== TRUE) {
				if ($routePart->isOptional() && $optionalPartCount === 1) {
					if ($routePart->getDefaultValue() === NULL) {
						return FALSE;
					}
					$skipOptionalParts = TRUE;
				} else {
					return FALSE;
				}
			}
			if ($routePart->getValue() !== NULL) {
				$matchResults[$routePart->getName()] = $routePart->getValue();
			}
		}
		if (strlen($routePath) > 0) {
			return FALSE;
		}
		$this->matchResults = \F3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($this->defaults, $matchResults);
		return TRUE;
	}

	/**
	 * Checks whether $routeValues can be resolved to a corresponding uri.
	 * If all Route Parts can resolve one or more of the $routeValues, TRUE is
	 * returned and $this->matchingURI contains the generated URI (excluding
	 * protocol and host).
	 *
	 * @param array $routeValues An array containing key/value pairs to be resolved to uri segments
	 * @return boolean TRUE if this Route corresponds to the given $routeValues, otherwise FALSE
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see getMatchingUri()
	 */
	public function resolves(array $routeValues) {
		$this->matchingUri = NULL;
		if ($this->uriPattern === NULL) {
			return FALSE;
		}
		if (!$this->isParsed) {
			$this->parse();
		}

		$matchingUri = '';
		$requireOptionalRouteParts = FALSE;
		$matchingOptionalUriPortion = '';
		foreach ($this->routeParts as $routePart) {
			if (!$routePart->resolve($routeValues)) {
				if (!$routePart->hasDefaultValue()) {
					return FALSE;
				}
			}
			if (!$routePart->isOptional()) {
				$matchingUri .= $routePart->hasValue() ? $routePart->getValue() : $routePart->getDefaultValue();
				$requireOptionalRouteParts = FALSE;
				continue;
			}
			if ($routePart->hasValue() && $routePart->getValue() !== $routePart->getDefaultValue()) {
				$matchingOptionalUriPortion .= $routePart->getValue();
				$requireOptionalRouteParts = TRUE;
			} else {
				$matchingOptionalUriPortion .= $routePart->getDefaultValue();
			}
			if ($requireOptionalRouteParts) {
				$matchingUri .= $matchingOptionalUriPortion;
				$matchingOptionalUriPortion = '';
			}
		}

			// Remove remaining route values of applied default values:
		foreach ($this->defaults as $key => $defaultValue) {
			if (isset($routeValues[$key])) {
				if (strtolower($routeValues[$key]) !== strtolower($defaultValue)) {
					return FALSE;
				}
				unset($routeValues[$key]);
			}
			if (isset($routeValues['@format']) && $routeValues['@format'] === '') {
				unset($routeValues['@format']);
			}
		}

			// add query string
		if (count($routeValues) > 0) {
			$routeValues = $this->convertDomainObjectsToIdentityArrays($routeValues);
			$matchingUri .= '?' . http_build_query($routeValues, NULL, '&');
		}
		$this->matchingUri = $matchingUri;
		return TRUE;
	}

	/**
	 * Recursively iterates through the specified routeValues and turns objects
	 * into an arrays containing the UUID of the domain object.
	 *
	 * @param array $routeValues The routeValues to be iterated over
	 * @return array The modified routeValues array
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function convertDomainObjectsToIdentityArrays(array $routeValues) {
		foreach ($routeValues as $routeValueKey => $routeValue) {
			if (is_object($routeValue)) {
				$uuid = $this->persistenceManager->getIdentifierByObject($routeValue);
				if ($uuid === FALSE) {
					throw new \F3\FLOW3\MVC\Exception\InvalidArgumentValueException('Route value "' . $routeValueKey . '" is an object but is unknown to the persistence manager.', 1242417960);
				}
				$routeValues[$routeValueKey] = array('__identity' => $uuid);
			} elseif (is_array($routeValue)) {
				$routeValues[$routeValueKey] = $this->convertDomainObjectsToIdentityArrays($routeValue);
			}
		}
		return $routeValues;
	}

	/**
	 * Iterates through all segments in $this->uriPattern and creates
	 * appropriate RoutePart instances.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function parse() {
		if ($this->isParsed || $this->uriPattern === NULL || $this->uriPattern === '') {
			return;
		}
		$this->routeParts = array();
		$currentRoutePartIsOptional = FALSE;
		if (substr($this->uriPattern, -1) === '/') {
			throw new \F3\FLOW3\MVC\Exception\InvalidUriPatternException('The URI pattern "' . $this->uriPattern . '" of route "' . $this->getName() . '" ends with a slash, which is not allowed. You can put the trailing slash in brackets to make it optional.', 1234782997);
		}
		if ($this->uriPattern[0] === '/') {
			throw new \F3\FLOW3\MVC\Exception\InvalidUriPatternException('The URI pattern "' . $this->uriPattern . '" of route "' . $this->getName() . '" starts with a slash, which is not allowed.', 1234782983);
		}

		$matches = array();
		preg_match_all(self::PATTERN_EXTRACTROUTEPARTS, $this->uriPattern, $matches, PREG_SET_ORDER);

		$lastRoutePart = NULL;
		foreach ($matches as $match) {
			$routePartType = empty($match['dynamic']) ? self::ROUTEPART_TYPE_STATIC : self::ROUTEPART_TYPE_DYNAMIC;
			$routePartName = $match['content'];
			if (!empty($match['optionalStart'])) {
				if ($lastRoutePart !== NULL && $lastRoutePart->isOptional()) {
					throw new \F3\FLOW3\MVC\Exception\InvalidUriPatternException('the URI pattern "' . $this->uriPattern . '" of route "' . $this->getName() . '" contains succesive optional Route sections, which is not allowed.', 1234562050);
				}
				$currentRoutePartIsOptional = TRUE;
			}
			$routePart = NULL;
			switch ($routePartType) {
				case self::ROUTEPART_TYPE_DYNAMIC:
					if ($lastRoutePart instanceof \F3\FLOW3\MVC\Web\Routing\DynamicRoutePartInterface) {
						throw new \F3\FLOW3\MVC\Exception\InvalidUriPatternException('the URI pattern "' . $this->uriPattern . '" of route "' . $this->getName() . '" contains succesive Dynamic Route Parts, which is not allowed.', 1218446975);
					}
					if (isset($this->routePartsConfiguration[$routePartName]['handler'])) {
						$routePart = $this->objectManager->get($this->routePartsConfiguration[$routePartName]['handler']);
						if (!$routePart instanceof \F3\FLOW3\MVC\Web\Routing\DynamicRoutePartInterface) {
							throw new \F3\FLOW3\MVC\Exception\InvalidRoutePartHandlerException('routePart handlers must implement "\F3\FLOW3\MVC\Web\Routing\DynamicRoutePartInterface" in route "' . $this->getName() . '"', 1218480972);
						}
					} else {
						$routePart = $this->objectManager->create('F3\FLOW3\MVC\Web\Routing\DynamicRoutePart');
					}
					if (isset($this->defaults[$routePartName])) {
						$routePart->setDefaultValue($this->defaults[$routePartName]);
					}
					break;
				case self::ROUTEPART_TYPE_STATIC:
					$routePart = $this->objectManager->create('F3\FLOW3\MVC\Web\Routing\StaticRoutePart');
					if ($lastRoutePart !== NULL && $lastRoutePart instanceof \F3\FLOW3\MVC\Web\Routing\DynamicRoutePartInterface) {
						$lastRoutePart->setSplitString($routePartName);
					}
			}
			$routePart->setName($routePartName);
			$routePart->setOptional($currentRoutePartIsOptional);
			if ($this->lowerCase) {
				$routePart->setLowerCase(TRUE);
			}
			if (isset($this->routePartsConfiguration[$routePartName]['options'])) {
				$routePart->setOptions($this->routePartsConfiguration[$routePartName]['options']);
			}
			if (isset($this->routePartsConfiguration[$routePartName]['toLowerCase'])) {
				$routePart->setLowerCase($this->routePartsConfiguration[$routePartName]['toLowerCase']);
			}

			$this->routeParts[] = $routePart;
			if (!empty($match['optionalEnd'])) {
				if (!$currentRoutePartIsOptional) {
					throw new \F3\FLOW3\MVC\Exception\InvalidUriPatternException('The URI pattern "' . $this->uriPattern . '" of route "' . $this->getName() . '" contains an unopened optional section.', 1234564495);
				}
				$currentRoutePartIsOptional = FALSE;
			}
			$lastRoutePart = $routePart;
		}
		if ($currentRoutePartIsOptional) {
			throw new \F3\FLOW3\MVC\Exception\InvalidUriPatternException('The URI pattern "' . $this->uriPattern . '" of route "' . $this->getName() . '" contains an unterminated optional section.', 1234563922);
		}
		$this->isParsed = TRUE;
	}
}

?>
