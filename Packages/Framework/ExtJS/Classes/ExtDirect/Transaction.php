<?php
declare(ENCODING = 'utf-8');
namespace F3\ExtJS\ExtDirect;

/*                                                                        *
 * This script belongs to the FLOW3 package "ExtJS".                      *
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
 * An Ext Direct transaction
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class Transaction {

	/**
	 * @inject
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @inject
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * The direct request this transaction belongs to
	 *
	 * @var \F3\ExtJS\ExtDirect\Request
	 */
	protected $request;

	/**
	 * The controller / class to use
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * The action / method to execute
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The arguments to be passed to the method
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The transaction ID to associate with this request
	 *
	 * @var integer
	 */
	protected $tid;

	/**
	 * Constructs the Transaction
	 *
	 * @param \F3\ExtJS\ExtDirect\Request $request The direct request this transaction belongs to
	 * @param string $action The "action" – the "controller object name" in FLOW3 terms
	 * @param string $method The "method" – the "action name" in FLOW3 terms
	 * @param array $data Numeric array of arguments which are eventually passed to the FLOW3 action method
	 * @param mixed $tid The ExtDirect transaction id
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct(\F3\ExtJS\ExtDirect\Request $request, $action, $method, array $data, $tid) {
		$this->request = $request;
		$this->action = $action;
		$this->method = $method;
		$this->data = $data;
		$this->tid = $tid;
	}

	/**
	 * Build a web request for dispatching this Ext Direct transaction
	 *
	 * @return \F3\FLOW3\MVC\Web\Request A web request for this transaction
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function buildRequest() {
		$request = $this->objectManager->create('F3\FLOW3\MVC\Web\Request');
		$request->setControllerObjectName($this->getControllerObjectName());
		$request->setControllerActionName($this->getMethod());
		$request->setFormat('extdirect');
		$request->setArguments($this->getArguments());
		return $request;
	}

	/**
	 * Build a response for dispatching this Ext Direct transaction
	 *
	 * @return \F3\ExtJS\ExtDirect\TransactionResponse A response for dispatching this transaction
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function buildResponse() {
		return $this->objectManager->create('F3\ExtJS\ExtDirect\TransactionResponse');
	}

	/**
	 * Getter for action
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Getter for method
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Getter for data
	 *
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Getter for type
	 *
	 * @return string The transaction type, currently always "rpc"
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getType() {
		return 'rpc';
	}

	/**
	 * Getter for tid
	 *
	 * @return integer
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getTid() {
		return $this->tid;
	}


	/**
	 * Getter for the controller object name
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getControllerObjectName() {
		return 'F3\\' . str_replace('_', '\\', $this->action);
	}

	/**
	 * Ext Direct does not provide named arguments by now, so we have
	 * to map them by reflecting on the action parameters.
	 *
	 * @return array The mapped arguments
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getArguments() {
		if ($this->data === array()) {
			return array();
		}

		$arguments = array();

		if (!$this->request->isFormPost()) {
			$parameters = $this->reflectionService->getMethodParameters($this->getControllerObjectName(), $this->method . 'Action');

			// TODO Add checks for parameters
			foreach ($parameters as $name => $options) {
				$parameterIndex = $options['position'];
				if (isset($this->data[$parameterIndex])) {
					$arguments[$name] = $this->convertObjectToArray($this->data[$parameterIndex]);
				}
			}

		} else {
			// TODO Reuse setArgumentsFromRawRequestData from Web/RequestBuilder
		}

		return $arguments;
	}

	/**
	 * Convert an object to an array recursively
	 *
	 * @param stdClass $object The object to convert
	 * @return array The object converted to an array
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function convertObjectToArray($object) {
		return json_decode(json_encode($object), TRUE);
	}
}
?>