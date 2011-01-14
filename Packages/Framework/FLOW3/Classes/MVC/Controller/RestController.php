<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\MVC\Controller;

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
 * An action controller for RESTful web services
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RestController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * The current request
	 * @var \F3\FLOW3\MVC\Web\Request
	 */
	protected $request;

	/**
	 * The response which will be returned by this action controller
	 * @var \F3\FLOW3\MVC\Web\Response
	 */
	protected $response;

	/**
	 * Name of the action method argument which acts as the resource for the
	 * RESTful controller. If an argument with the specified name is passed
	 * to the controller, the show, update and delete actions can be triggered
	 * automatically.
	 *
	 * @var string
	 */
	protected $resourceArgumentName = 'resource';

	/**
	 * Determines the action method and assures that the method exists.
	 *
	 * @return string The action method name
	 * @throws \F3\FLOW3\MVC\Exception\NoSuchActionException if the action specified in the request object does not exist (and if there's no default action either).
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function resolveActionMethodName() {
		if ($this->request->getControllerActionName() === 'index') {
			$actionName = 'index';
			switch ($this->request->getMethod()) {
				case 'HEAD':
				case 'GET' :
					$actionName = ($this->request->hasArgument($this->resourceArgumentName)) ? 'show' : 'list';
				break;
				case 'POST' :
					$actionName = 'create';
				break;
				case 'PUT' :
					if (!$this->request->hasArgument($this->resourceArgumentName)) {
						$this->throwStatus(400, NULL, 'No resource specified');
					}
					$actionName = 'update';
				break;
				case 'DELETE' :
					if (!$this->request->hasArgument($this->resourceArgumentName)) {
						$this->throwStatus(400, NULL, 'No resource specified');
					}
					$actionName = 'delete';
				break;
			}
			$this->request->setControllerActionName($actionName);
		}
		return parent::resolveActionMethodName();
	}

	/**
	 * Redirects the web request to another uri.
	 *
	 * NOTE: This method only supports web requests and will throw an exception
	 * if used with other request types.
	 *
	 * @param mixed $uri Either a string representation of a URI or a \F3\FLOW3\Property\DataType\Uri object
	 * @param integer $delay (optional) The delay in seconds. Default is no delay.
	 * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other"
	 * @throws \F3\FLOW3\MVC\Exception\UnsupportedRequestTypeException If the request is not a web request
	 * @throws \F3\FLOW3\MVC\Exception\StopActionException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	protected function redirectToUri($uri, $delay = 0, $statusCode = 303) {
			// the parent method throws the exception, but we need to act afterwards
			// thus the code in catch - it's the expected state
		try {
			parent::redirectToUri($uri, $delay, $statusCode);
		} catch (\F3\FLOW3\MVC\Exception\StopActionException $exception) {
			if ($this->request->getFormat() === 'json') {
				$this->response->setContent('');
			}
			throw $exception;
		}
	}
}
?>