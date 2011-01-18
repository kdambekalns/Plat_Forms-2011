<?php
declare(ENCODING = 'utf-8');
namespace F3\CaP\Tests\Unit\Domain\Repository;

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
 * @origin: M
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ConferenceRepositoryTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function dateStringProvider() {
		return array(
			array('20110118', new \DateTime('2011-01-18')),
			array('2011/01/18', new \DateTime('2011-01-18')),
			array('2011-01-18', new \DateTime('2011-01-18')),
			array('18.01.2011', new \DateTime('2011-01-18')),

			array('2011/1/18', new \DateTime('2011-01-18')),
			array('2011-1-18', new \DateTime('2011-01-18')),
			array('18.1.2011', new \DateTime('2011-01-18'))
		);
	}

	/**
	 * @test
	 * @dataProvider dateStringProvider
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function parseDateStringParsesExpectedFormats($dateString, $expectedDate) {
		$conferenceRepository = $this->getAccessibleMock('F3\CaP\Domain\Repository\ConferenceRepository', array('dummy'));
		$parsedDate = $conferenceRepository->_call('parseDateString', $dateString);
		$this->assertEquals($parsedDate, $expectedDate);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function parseDateStringReturnsNullOnError() {
		$conferenceRepository = $this->getAccessibleMock('F3\CaP\Domain\Repository\ConferenceRepository', array('dummy'));
		$parsedDate = $conferenceRepository->_call('parseDateString', 'not a date');
		$this->assertNull($parsedDate);
	}

	/**
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function userQueryProvider() {
		return array(
			array(
				'',
				array(
					'terms' => array(),
					'categories' => array(),
					'options' => array(),
					'from' => NULL,
					'until' => NULL,
					'region' => NULL
					)
				),
			array(
				'foo bar baz',
				array(
					'terms' => array('foo', 'bar', 'baz'),
					'categories' => array(),
					'options' => array(),
					'from' => NULL,
					'until' => NULL,
					'region' => NULL
					)
				),
			array(
				'foo cat:science bar',
				array(
					'terms' => array('foo', 'bar'),
					'categories' => array('science'),
					'options' => array(),
					'from' => NULL,
					'until' => NULL,
					'region' => NULL
					)
				),
			array(
				'foo cat:science opt:withsub bar reg:country',
				array(
					'terms' => array('foo', 'bar'),
					'categories' => array('science'),
					'options' => array('withsub' => TRUE),
					'from' => NULL,
					'until' => NULL,
					'region' => 'country'
					)
				),
			array(
				'foo cat:science from:27.4.1977 until:2000-09-15 bar reg:50',
				array(
					'terms' => array('foo', 'bar'),
					'categories' => array('science'),
					'options' => array(),
					'from' => new \DateTime('1977-04-27'),
					'until' => new \DateTime('2000-09-15'),
					'region' => 50
					)
				),
			array(
					// check handling of invalid from date and region
				'foo cat:science from:yesterday until:2000-09-15 bar reg:foobar',
				array(
					'terms' => array('foo', 'bar'),
					'categories' => array('science'),
					'options' => array(),
					'from' => NULL,
					'until' => new \DateTime('2000-09-15'),
					'region' => NULL
					)
				),
		);
	}

	/**
	 * @test
	 * @dataProvider userQueryProvider
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function parseUserQueryParsesQueriesAsExpected($queryString, $expectedQuery) {
		$conferenceRepository = $this->getAccessibleMock('F3\CaP\Domain\Repository\ConferenceRepository', array('dummy'));
		$parsedQuery = $conferenceRepository->_call('parseUserQuery', $queryString);
		$this->assertEquals($parsedQuery, $expectedQuery);
	}
}

?>