<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\MVC;

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
 * The interface for a request handler
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Robert Lemke <robert@typo3.org>
 * @api
 */
interface RequestHandlerInterface {

	/**
	 * Handles a raw request and sends the respsonse.
	 *
	 * @return void
	 * @api
	 */
	public function handleRequest();

	/**
	 * Checks if the request handler can handle the current request.
	 *
	 * @return mixed TRUE or an integer > 0 if it can handle the request, otherwise FALSE or an integer < 0
	 * @api
	 */
	public function canHandleRequest();

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request. An integer > 0 means "I want to handle this request" where
	 * "100" is default. "0" means "I am a fallback solution".
	 *
	 * @return integer The priority of the request handler
	 * @api
	 */
	public function getPriority();
}

?>