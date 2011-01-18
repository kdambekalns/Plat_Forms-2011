<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "CaP".                        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The Conference Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @origin: M
 */
class ConferenceController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @inject
	 * @var \F3\CaP\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @inject
	 * @var \F3\CaP\Domain\Repository\ConferenceRepository
	 */
	protected $conferenceRepository;

	/**
	 * @inject
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \F3\FLOW3\Security\Account
	 */
	protected $account;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeAction() {
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				$this->account = $token->getAccount();
			}
		}
	}

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * @param \F3\FLOW3\MVC\View\ViewInterface $view The view to be initialized
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeView(\F3\FLOW3\MVC\View\ViewInterface $view) {
		$view->assign('account', $this->account);
	}

	/**
	 * Displays the main screen
	 *
	 * @param \F3\CaP\Domain\Model\Category
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function indexAction(\F3\CaP\Domain\Model\Category $category = NULL) {
		$this->view->assign('categories', $this->categoryRepository->findByParent($category));

		if ($category === NULL) {
			$this->view->assign('conferences', $this->conferenceRepository->findCurrent());
		} else {
			$this->view->assign('conferences', $this->conferenceRepository->findCurrentByCategory($category));
		}

		$categoryPath = array();
		if ($category !== NULL) {
			do {
				array_unshift($categoryPath, $category);
			} while (($category = $category->getParent()) !== NULL);
		}
		$this->view->assign('categoryPath', $categoryPath);
	}

	/**
	 * Displays the main screen
	 *
	 * @param \F3\CaP\Domain\Model\Conference $conference
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function showAction(\F3\CaP\Domain\Model\Conference $conference) {
		$this->view->assign('conference', $conference);
	}
}

?>