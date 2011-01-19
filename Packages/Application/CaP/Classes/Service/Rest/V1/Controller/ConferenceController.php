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
	 * @inject
	 * @var \F3\CaP\Domain\Repository\ConferenceRepository
	 */
	protected $conferenceRepository;

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
	 * Lists all conferences
	 *
	 * @return void
	 */
	public function listAction() {
		$this->listConferences($this->conferenceRepository->findAll());
	}

	/**
	 * Lists all conferences of the given category
	 *
	 * \F3\CaP\Domain\Model\Category $category
	 * @return void
	 */
	public function listByCategoryAction(\F3\CaP\Domain\Model\Category $category) {
		$this->listConferences($this->conferenceRepository->findByCategory($category));
	}

	/**
	 * Prepares the view to list the given conferences
	 *
	 * @param \F3\FLOW3\Persistence\QueryResult $conferences
	 * @return void
	 */
	protected function listConferences(\F3\FLOW3\Persistence\QueryResult $conferences) {
		$conferencesArray = array();
		foreach ($conferences as $conference) {
			$categoriesArray = array();
			foreach ($conference->getCategories() as $category) {
				$categoriesArray[] = array(
					'name' => $category->getName(),
					'details' => $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('category' => $category), 'Category')
				);
			}

			$conferencesArray[] = array(
				'name' => $conference->getName(),
				'startdate' => $conference->getStartDate()->format('Y-m-d'),
				'enddate' => $conference->getEndDate()->format('Y-m-d'),
				'categories' => $categoriesArray,
				'details' => $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('conference' => $conference))
			);
		}

		$this->view->assign('value', $conferencesArray);

		if (count($conferencesArray) === 0) {
			$this->response->setStatus(204);
		}
	}

	/**
	 * Shows the specified conference
	 *
	 * @param \F3\CaP\Domain\Model\Conference $conference
	 * @return string View output for the specified conference
	 */
	public function showAction(\F3\CaP\Domain\Model\Conference $conference) {
		$categoriesArray = array();
		foreach ($conference->getCategories() as $category) {
			$categoriesArray[] = array(
				'name' => $category->getName(),
				'details' => $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('category' => $category), 'Category')
			);
		}

		$conferenceArray = array(
			'version' => $conference->getVersion(),
			'id' => $conference->getId(),
			'name' => $conference->getName(),
			'creator' => $this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show', array('member' => $conference->getCreator()), 'Member'),
			'series' => NULL,
			'startdate' => $conference->getStartDate()->format('Y-m-d'),
			'enddate' => $conference->getEndDate()->format('Y-m-d'),
			'categories' => $categoriesArray,
			'description' => $conference->getDescription(),
			'location' => '',
			'gps' => '',
			'venue' => '',
			'accomodation' => '',
			'howtofind' => ''
		);

		$this->view->setConfiguration(array('value' => array('_exclude' => array('attendee'))));
		$this->view->assign('value', $conferenceArray);
	}

	/**
	 * Creates a new conference
	 *
	 * If FLOW3 would be used in a regular fashion (not depending on the specialities of this Spec
	 * regarding the incoming JSON format), this action would receive a ready-built Conference
	 * object rather than a simple array.
	 *
	 * @param array $conference
	 * @return void
	 */
	public function createAction(array $conference) {
		$conferenceArray = array(
			'name' => $conference['name'] ?: '',
			'description' => $conference['description'] ?: '',
			'creator' => $conference['creator'] ?: '',
			'startDate' => $conference['startdate'] ? \F3\CaP\Utility\DateConverter::createDateFromString($conference['startdate']) : NULL,
			'endDate' => $conference['enddate'] ? \F3\CaP\Utility\DateConverter::createDateFromString($conference['enddate']) : NULL,
		);

		if (isset($conference->categories)) {
			foreach ($conference['categories'] as $categoryUuid) {
				$conferenceArray['categories'][] = array('__identitiy' => $categoryUuid);
			}
		}

		try {
			$conference = $this->propertyMapper->map(array_keys($conferenceArray), $conferenceArray, 'F3\CaP\Domain\Model\Conference');
			if ($conference !== FALSE) {
				$this->conferenceRepository->add($conference);
				$this->forward('show', NULL, NULL, array('conference' => $conference));
			}
		} catch (\InvalidArgumentException $exception) {
		}
		$this->response->setStatus(400);
	}

	/**
	 * Updates an existing conference
	 *
	 * @param array $conference
	 * @return void
	 */
	public function updateAction(array $conference) {

	}
}

?>