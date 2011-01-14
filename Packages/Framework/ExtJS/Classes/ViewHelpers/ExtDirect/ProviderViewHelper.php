<?php
declare(ENCODING = 'utf-8');
namespace F3\ExtJS\ViewHelpers\ExtDirect;

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
 * Ext Direct Provider view helper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @api
 */
class ProviderViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 */
	protected $localReflectionService;

	/**
	 * Inject the Reflection service
	 *
	 * A _private_ property "reflectionService" already exists in the AbstractViewHelper,
	 * therefore we need to switch to another property name.
	 *
	 * @param \F3\FLOW3\Reflection\ReflectionService $reflectionService Reflection service
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function injectLocalReflectionService(\F3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->localReflectionService = $reflectionService;
	}

	/**
	 * Returns the JavaScript to declare the Ext Direct provider for all
	 * controller actions that are annotated with @extdirect
	 *
	 * = Examples =
	 *
	 * <code title="Simple">
	 * {namespace ext=F3\ExtJS\ViewHelpers}
	 *  ...
	 * <script type="text/javascript">
	 * <ext:extdirect.provider />
	 * </script>
	 *  ...
	 * </code>
	 *
	 * TODO Cache ext direct provider config
	 * @param string $namespace The base ExtJS namespace (with dots) for the direct provider methods
	 * @return string JavaScript needed to include Ext Direct provider
	 * @api
	 */
	public function render($namespace = 'F3') {
		$providerConfig = array(
			'url' => '?F3_ExtJS_ExtDirectRequest=1',
			'type' => 'remoting',
			'namespace' => $namespace,
			'actions' => array()
		);
		$controllerClassNames = $this->localReflectionService->getAllImplementationClassNamesForInterface('F3\FLOW3\MVC\Controller\ControllerInterface');
		foreach ($controllerClassNames as $controllerClassName) {
			$methodNames = $this->localReflectionService->getClassMethodNames($controllerClassName);
			foreach ($methodNames as $methodName) {
				$methodTagsValues = $this->localReflectionService->getMethodTagsValues($controllerClassName, $methodName);
				if (isset($methodTagsValues['extdirect'])) {
					$methodParameters = $this->localReflectionService->getMethodParameters($controllerClassName, $methodName);
					$requiredMethodParametersCount = 0;
					foreach ($methodParameters as $methodParameter) {
						if ($methodParameter['optional'] === TRUE) {
							break;
						}
						$requiredMethodParametersCount ++;
					}
					$extDirectAction = str_replace('\\', '_', str_replace('F3\\', '', $controllerClassName));
					$providerConfig['actions'][$extDirectAction][] = array(
						'name' => substr($methodName, 0, -6),
						'len' => $requiredMethodParametersCount
					);
				}
			}
		}

		return 'Ext.Direct.addProvider(' . json_encode($providerConfig) . ');' . chr(10);
	}
}

?>