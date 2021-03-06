<?php
declare(ENCODING = 'utf-8') ;
namespace F3\Fluid\Core\Widget;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
abstract class AbstractWidgetViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper implements \F3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface {

	/**
	 * The Controller associated to this widget.
	 * This needs to be filled by the individual subclass by an @inject
	 * annotation.
	 *
	 * @var F3\Fluid\Core\Widget\AbstractWidgetController
	 * @api
	 */
	protected $controller;

	/**
	 * If set to TRUE, it is an AJAX widget.
	 *
	 * @var boolean
	 * @api
	 */
	protected $ajaxWidget = FALSE;

	/**
	 * @var F3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	private $ajaxWidgetContextHolder;

	/**
	 * @var F3\FLOW3\Object\ObjectManagerInterface
	 */
	private $objectManager;

	/**
	 * @var F3\Fluid\Core\Widget\WidgetContext
	 */
	private $widgetContext;

	/**
	 * @param F3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectAjaxWidgetContextHolder(\F3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder) {
		$this->ajaxWidgetContextHolder = $ajaxWidgetContextHolder;
	}

	/**
	 * @param F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param F3\Fluid\Core\Widget\WidgetContext $widgetContext
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectWidgetContext(\F3\Fluid\Core\Widget\WidgetContext $widgetContext) {
		$this->widgetContext = $widgetContext;
	}

	/**
	 * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
	 *
	 * @param array $renderMethodParameters the parameters of the render() method.
	 * @return string the rendered ViewHelper.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArgumentsAndRender(array $renderMethodParameters) {
		$this->validateArguments();
		$this->initialize();
		$this->initializeWidgetContext();

		return $this->callRenderMethod($renderMethodParameters);
	}

	/**
	 * Initialize the Widget Context, before the Render method is called.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	private function initializeWidgetContext() {
		if ($this->ajaxWidget === TRUE) {
			$this->ajaxWidgetContextHolder->store($this->widgetContext);
		}

		$this->widgetContext->setWidgetConfiguration($this->getWidgetConfiguration());
		$this->initializeWidgetIdentifier();

		$controllerObjectName = ($this->controller instanceof \F3\FLOW3\AOP\ProxyInterface) ? $this->controller->FLOW3_AOP_Proxy_getProxyTargetClassName() : get_class($this->controller);
		$this->widgetContext->setControllerObjectName($controllerObjectName);
	}

	/**
	 * Stores the syntax tree child nodes in the Widget Context, so they can be
	 * rendered with <f:widget.renderChildren> lateron.
	 *
	 * @param array $childNodes The SyntaxTree Child nodes of this ViewHelper.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setChildNodes(array $childNodes) {
		$rootNode = $this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\RootNode');
		foreach ($childNodes as $childNode) {
			$rootNode->addChildNode($childNode);
		}
		$this->widgetContext->setViewHelperChildNodes($rootNode, $this->getRenderingContext());
	}

	/**
	 * Generate the configuration for this widget. Override to adjust.
	 *
	 * @return array
	 * @api
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getWidgetConfiguration() {
		return $this->arguments;
	}

	/**
	 * Initiate a sub request to $this->controller. Make sure to fill $this->controller
	 * via Dependency Injection.
	 *
	 * @return F3\FLOW3\MVC\ResponseInterface the response of this request.
	 * @api
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function initiateSubRequest() {
		if (!($this->controller instanceof \F3\Fluid\Core\Widget\AbstractWidgetController)) {
			throw new \F3\Fluid\Core\Widget\Exception\MissingControllerException('initiateSubRequest() can not be called if there is no controller inside $this->controller. Make sure to add the @inject annotation in your widget class.', 1284401632);
		}

		$subRequest = $this->objectManager->create('F3\Fluid\Core\Widget\WidgetRequest');
		$subRequest->setWidgetContext($this->widgetContext);
		$this->passArgumentsToSubRequest($subRequest);

		$subResponse = $this->objectManager->create('F3\FLOW3\MVC\Web\Response');
		$this->controller->processRequest($subRequest, $subResponse);
		return $subResponse;
	}

	/**
	 * Pass the arguments of the widget to the subrequest.
	 *
	 * @param \F3\Fluid\Core\Widget\WidgetRequest $subRequest
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	private function passArgumentsToSubRequest(\F3\Fluid\Core\Widget\WidgetRequest $subRequest) {
		$arguments = $this->controllerContext->getRequest()->getArguments();
		$widgetIdentifier = $this->widgetContext->getWidgetIdentifier();
		if (isset($arguments[$widgetIdentifier])) {
			if (isset($arguments[$widgetIdentifier]['action'])) {
				$subRequest->setControllerActionName($arguments[$widgetIdentifier]['action']);
				unset($arguments[$widgetIdentifier]['action']);
			}
			$subRequest->setArguments($arguments[$widgetIdentifier]);
		}
	}

	/**
	 * The widget identifier is unique on the current page, and is used
	 * in the URI as a namespace for the widget's arguments.
	 *
	 * @return string the widget identifier for this widget
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @return void
	 * @todo clean up, and make it somehow more routing compatible.
	 */
	private function initializeWidgetIdentifier() {
		if (!$this->viewHelperVariableContainer->exists('F3\Fluid\Core\Widget\AbstractWidgetViewHelper', 'nextWidgetNumber')) {
			$widgetCounter = 0;
		} else {
			$widgetCounter = $this->viewHelperVariableContainer->get('F3\Fluid\Core\Widget\AbstractWidgetViewHelper', 'nextWidgetNumber');
		}
		$widgetIdentifier = '__widget_' . $widgetCounter;
		$this->viewHelperVariableContainer->addOrUpdate('F3\Fluid\Core\Widget\AbstractWidgetViewHelper', 'nextWidgetNumber', $widgetCounter + 1);

		$this->widgetContext->setWidgetIdentifier($widgetIdentifier);
	}
}

?>