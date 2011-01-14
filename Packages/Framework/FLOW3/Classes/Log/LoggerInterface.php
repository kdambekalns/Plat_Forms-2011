<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Log;

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
 * Contract for a basic logger interface
 *
 * The severities are (according to RFC3164) the PHP constants:
 *   LOG_EMERG   # Emergency: system is unusable
 *   LOG_ALERT   # Alert: action must be taken immediately
 *   LOG_CRIT    # Critical: critical conditions
 *   LOG_ERR     # Error: error conditions
 *   LOG_WARNING # Warning: warning conditions
 *   LOG_NOTICE  # Notice: normal but significant condition
 *   LOG_INFO    # Informational: informational messages
 *   LOG_DEBUG   # Debug: debug-level messages
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Robert Lemke <robert@typo3.org>
 * @api
 */
interface LoggerInterface {

	/**
	 * Adds a backend to which the logger sends the logging data
	 *
	 * @param \F3\FLOW3\Log\Backend\BackendInterface $backend A backend implementation
	 * @return void
	 * @api
	 */
	public function addBackend(\F3\FLOW3\Log\Backend\BackendInterface $backend);

	/**
	 * Runs the close() method of a backend and removes the backend
	 * from the logger.
	 *
	 * @param \F3\FLOW3\Log\Backend\BackendInterface $backend The backend to remove
	 * @return void
	 * @throws \F3\FLOW3\Log\Exception\NoSuchBackendException if the given backend is unknown to this logger
	 * @api
	 */
	public function removeBackend(\F3\FLOW3\Log\Backend\BackendInterface $backend);

	/**
	 * Writes the given message along with the additional information into the log.
	 *
	 * @param string $message The message to log
	 * @param integer $severity An integer value, one of the LOG_* constants
	 * @param mixed $additionalData A variable containing more information about the event to be logged
	 * @param string $packageKey Key of the package triggering the log (determined automatically if not specified)
	 * @param string $className Name of the class triggering the log (determined automatically if not specified)
	 * @param string $methodName Name of the method triggering the log (determined automatically if not specified)
	 * @return void
	 * @api
	 */
	public function log($message, $severity = LOG_INFO, $additionalData = NULL, $packageKey = NULL, $className = NULL, $methodName = NULL);

	/**
	 * Writes information about the given exception into the log.
	 *
	 * @param \Exception $exception The exception to log
	 * @return void
	 * @api
	 */
	public function logException(\Exception $exception);

}
?>