<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\Interceptor;

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
 * An interceptor adding the escape viewhelper to the suitable places.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Escape implements \F3\Fluid\Core\Parser\InterceptorInterface {

	/**
	 * Is the interceptor enabled right now?
	 * @var boolean
	 */
	protected $interceptorEnabled = TRUE;

	/**
	 * A stack of ViewHelperNodes which currently disable the interceptor.
	 * Needed to enable the interceptor again.
	 * 
	 * @var array<\F3\Fluid\Core\Parser\SyntaxTree\NodeInterface>
	 */
	protected $viewHelperNodesWhichDisableTheInterceptor = array();
	
	/**
	 * Inject object factory
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Adds a ViewHelper node using the EscapeViewHelper to the given node.
	 * If "escapingInterceptorEnabled" in the ViewHelper is FALSE, will disable itself inside the ViewHelpers body.
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @return \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function process(\F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node, $interceptorPosition) {
		if ($interceptorPosition === \F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER) {
			if (!$node->getUninitializedViewHelper()->isEscapingInterceptorEnabled()) {
				$this->interceptorEnabled = FALSE;
				$this->viewHelperNodesWhichDisableTheInterceptor[] = $node;
			}
		} elseif ($interceptorPosition === \F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER) {
			if (end($this->viewHelperNodesWhichDisableTheInterceptor) === $node) {
				array_pop($this->viewHelperNodesWhichDisableTheInterceptor);
				if (count($this->viewHelperNodesWhichDisableTheInterceptor) === 0) {
					$this->interceptorEnabled = TRUE;
				}
			}
		} elseif ($this->interceptorEnabled && $node instanceof \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode) {
			$node = $this->objectManager->create(
				'F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode',
				$this->objectManager->create('F3\Fluid\ViewHelpers\EscapeViewHelper'),
				array('value' => $node)
			);
		}
		return $node;
	}

	/**
	 * This interceptor wants to hook into object accessor creation, and opening / closing ViewHelpers.
	 *
	 * @return array Array of INTERCEPT_* constants
	 */
	public function getInterceptionPoints() {
		return array(
			\F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER,
			\F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
			\F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OBJECTACCESSOR
		);
	}
}
?>