<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Widget;

/*
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
 * Builds the WidgetRequest if an AJAX widget is called.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class WidgetRequestBuilder extends \F3\FLOW3\MVC\Web\RequestBuilder {

	/**
	 * @var F3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	private $ajaxWidgetContextHolder;

	/**
	 * @param F3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectAjaxWidgetContextHolder(\F3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder) {
		$this->ajaxWidgetContextHolder = $ajaxWidgetContextHolder;
	}

	/**
	 * Builds a widget request object from the raw HTTP information
	 *
	 * @return F3\Fluid\Core\Widget\WidgetRequest The widget request as an object
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function build() {
		$request = $this->objectManager->create('F3\Fluid\Core\Widget\WidgetRequest');
		$request->injectEnvironment($this->environment);
		$request->setMethod($this->environment->getRequestMethod());
		$this->setArgumentsFromRawRequestData($request);

		$rawGetArguments = $this->environment->getRawGetArguments();
			// TODO: rename to @action, to be consistent with normal naming?
		if (isset($rawGetArguments['action'])) {
			$request->setControllerActionName($rawGetArguments['action']);
		}

		$widgetContext = $this->ajaxWidgetContextHolder->get($rawGetArguments['f3-fluid-widget-id']);
		$request->setWidgetContext($widgetContext);
		return $request;
	}
}

?>