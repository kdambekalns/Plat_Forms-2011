<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser;

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
 * This interface is returned by \F3\Fluid\Core\Parser\TemplateParser->parse()
 * method and is a parsed template
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface ParsedTemplateInterface {

	/**
	 * Render the parsed template with rendering context
	 *
	 * @param F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext The rendering context to use
	 * @return Rendered string
	 */
	public function render(\F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext);

	/**
	 * Returns a variable container used in the PostParse Facet.
	 *
	 * @return \F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	// TODO
	public function getVariableContainer(); // rename to getPostParseVariableContainer -- @internal definitely
}

?>