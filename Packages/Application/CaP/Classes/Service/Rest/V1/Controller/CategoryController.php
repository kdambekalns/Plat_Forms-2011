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
 * REST Controller for Category
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @origin: M
 */
class CategoryController extends \F3\FLOW3\MVC\Controller\RestController {

	/**
	 * @var string
	 */
	protected $resourceArgumentName = 'category';

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
	 * @inject
	 * @var \F3\CaP\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * Lists all top-level categories
	 *
	 * @return void
	 */
	public function listAction() {
		$toplevelCategories = array();

		foreach ($this->categoryRepository->findByParent(NULL) as $category) {
			$toplevelCategories[] = array(
				'name' => $category->getName(),
				'details' => (string)$this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('category' => $category))
			);
		}

		$this->view->assign('value', $toplevelCategories);

		if (count($toplevelCategories) === 0) {
			$this->response->setStatus(204);
		}
	}

	/**
	 * @param \F3\CaP\Domain\Model\Category
	 * @return void
	 */
	public function showAction(\F3\CaP\Domain\Model\Category $category) {
		$viewConfiguration = array();

		$parent = $category->getParent();
		if ($parent !== NULL) {
			$parentUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('category' => $parent));
			$viewConfiguration['value']['parent'] = $parentUri;
		}

		$viewConfiguration['value']['subcategories'] = array();
		foreach ($this->categoryRepository->findByParent($category) as $subCategory) {
			$uri = $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('category' => $subCategory));
			$viewConfiguration['value']['subcategories'][] = $uri;
		}

		$this->view->setConfiguration($viewConfiguration);
		$this->view->assign('value', $category);
	}
}

?>