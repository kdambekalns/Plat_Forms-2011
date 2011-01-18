<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Service\Rest\V1\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "CaP".                        *
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
 * REST Controller for Conference
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @origin: M
 */
class ConferenceController extends \F3\FLOW3\MVC\Controller\RestController {


	/**
	 * @var string
	 */
	protected $resourceArgumentName = 'conference';

	/**
	 * @var array
	 */
	protected $supportedFormats = array('json');

	/**
	 * @var array
	 */
	protected $viewFormatToObjectNameMap = array(
		 'json' => 'F3\FLOW3\MVC\View\JsonView',
	);

	/**
	 *
	 */
	public function listAction() {
		return 'jaja';
	}

	/**
	 * Shows the specified conference
	 *
	 * @param
	 * @return string View output for the specified conference
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function showAction(\F3\CaP\Domain\Model\Conference $conference) {
		$this->view->assign('value', $conference);
	}
}

?>