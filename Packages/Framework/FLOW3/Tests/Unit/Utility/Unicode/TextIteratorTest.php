<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Utility\Unicode;

/*                                                                        *
 * This script belongs to the FLOW3 package "PHP6".                       *
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
 * Testcase for the TextIterator port
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TextIteratorTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * Checks if a new instance with the default iterator type can be created
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function canCreateIteratorOfDefaultType() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('Some string');
		$this->assertType('F3\FLOW3\Utility\Unicode\TextIterator', $iterator);
	}

	/**
	 * Checks if a new instance iterating over characters can be created
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function instantiatingCharacterIteratorWorks() {
		$characterIterator = new \F3\FLOW3\Utility\Unicode\TextIterator('Some string', \F3\FLOW3\Utility\Unicode\TextIterator::CHARACTER );
		$this->assertType('F3\FLOW3\Utility\Unicode\TextIterator', $characterIterator);
	}

	/**
	 * Checks if a new instance iterating over words can be created
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function instantiatingWordIteratorWorks() {
		$wordIterator = new \F3\FLOW3\Utility\Unicode\TextIterator('Some string', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);
		$this->assertType('F3\FLOW3\Utility\Unicode\TextIterator', $wordIterator);
	}


	/**
	 * Checks if a new instance iterating over sentences can be created
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function instantiatingSentenceIteratorWorks() {
		$sentenceIterator = new \F3\FLOW3\Utility\Unicode\TextIterator('Some string', \F3\FLOW3\Utility\Unicode\TextIterator::SENTENCE );
		$this->assertType('F3\FLOW3\Utility\Unicode\TextIterator', $sentenceIterator);
	}

	/**
	 * Checks if a new instance iterating over lines can be created
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function instantiatingLineIteratorWorks() {
		$lineIterator = new \F3\FLOW3\Utility\Unicode\TextIterator('Some string', \F3\FLOW3\Utility\Unicode\TextIterator::LINE);
		$this->assertType('F3\FLOW3\Utility\Unicode\TextIterator', $lineIterator);
	}


	/**
	 * Checks if the constructor rejects an invalid iterator type
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function instantiatingIteratorWithInvalidTypeThrowsError() {
		try {
			$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('Some string', 948);
			$this->fail('Constructor did not reject invalid TextIterator type.');
		} catch (\F3\FLOW3\Error\Exception $exception) {
			$this->assertContains('Invalid iterator type in TextIterator constructor', $exception->getMessage(), 'Wrong error message.');
		}
	}

	/**
	 * Checks if character iteration basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function characterIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by character...', \F3\FLOW3\Utility\Unicode\TextIterator::CHARACTER);
		$iterator->rewind();
		$result = '';
		foreach ($iterator as $currentCharacter) {
			$result .= $currentCharacter;
		}
		$this->assertEquals('This is a test string. Let\'s iterate it by character...', $result, 'Character iteration didn\'t return the right values.');
	}

	/**
	 * Checks if word iteration basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function wordIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word...', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);
		$iterator->rewind();
		$result = '';
		foreach ($iterator as $currentWord) {
			$result .= $currentWord;
		}
		$this->assertEquals('This is a test string. Let\'s iterate it by word...', $result, 'Word iteration didn\'t return the right values.');
	}

	/**
	 * Checks if sentence iteration basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function sentenceIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by sentence...', \F3\FLOW3\Utility\Unicode\TextIterator::SENTENCE);
		$iterator->rewind();
		$result = '';
		foreach ($iterator as $currentSentence) {
			$result .= $currentSentence;
		}
		$this->assertEquals('This is a test string. Let\'s iterate it by sentence...', $result, 'Sentence iteration didn\'t return the right values.');
	}

	/**
	 * Checks if line iteration basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function lineIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator("This is a test string. \nLet's iterate \nit by line...", \F3\FLOW3\Utility\Unicode\TextIterator::LINE);
		$iterator->rewind();
		$result = '';
		foreach ($iterator as $currentLine) {
			$result .= $currentLine;
		}
		$this->assertEquals("This is a test string. \nLet's iterate \nit by line...", $result, 'Line iteration didn\'t return the right values.');
	}

	/**
	 * Checks if the offset method basically works with character iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function offsetInCharacterIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by character...', \F3\FLOW3\Utility\Unicode\TextIterator::CHARACTER);
		foreach ($iterator as $currentCharacter) {
			if ($currentCharacter == 'L') break;
		}
		$this->assertEquals($iterator->offset(), 23, 'Wrong offset returned in character iteration.');
	}

	/**
	 * Checks if the offset method basically works with word iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function offsetInWordIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word...', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);
		foreach ($iterator as $currentWord) {
			if ($currentWord == 'iterate') break;
		}
		$this->assertEquals($iterator->offset(), 29, 'Wrong offset returned in word iteration.');
	}

	/**
	 * Checks if the offset method basically works with sentence iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function offsetInSentenceIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word...', \F3\FLOW3\Utility\Unicode\TextIterator::SENTENCE);
		foreach ($iterator as $currentSentence) {
			if ($currentSentence == 'Let\'s iterate it by word.') break;
		}
		$this->assertEquals($iterator->offset(), 23, 'Wrong offset returned in sentence iteration.');
	}

	/**
	 * Checks if the "first" method basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function firstBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word...', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);
		$iterator->next();
		$this->assertEquals($iterator->first(), 'This', 'Wrong element returned by first().');
	}

	/**
	 * Checks if the "last" method basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function lastBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);
		$iterator->rewind();
		$this->assertEquals($iterator->last(), 'word', 'Wrong element returned by last().');
	}

	/**
	 * Checks if the "getAll" method basically works
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAllBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string.', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);

		$expectedResult = array(
			0 => 'This',
			1 => ' ',
			2 => 'is',
			3 => ' ',
			4 => 'a',
			5 => ' ',
			6 => 'test',
			7 => ' ',
			8 => 'string',
			9 => '.',
		);

		$this->assertEquals($iterator->getAll(), $expectedResult, 'Wrong element returned by getAll().');
	}

	/**
	 * Checks if the "isBoundary" method basically works with character iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isBoundaryInCharacterIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by character', \F3\FLOW3\Utility\Unicode\TextIterator::CHARACTER);
		$iterator->rewind();
		while ($iterator->valid()) {
			$this->assertFalse($iterator->isBoundary(), 'Character iteration has no boundary elements.');
			$iterator->next();
		}
	}

	/**
	 * Checks if the "isBoundary" method basically works with word iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isBoundaryInWordIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);
		$iterator->rewind();
		$this->assertFalse($iterator->isBoundary(), 'This element was a boundary element.');

		$iterator->next();
		$this->assertTrue($iterator->isBoundary(), 'This element was no boundary element.');
	}

	/**
	 * Checks if the "isBoundary" method basically works with sentence iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isBoundaryInSentenceIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by sentence', \F3\FLOW3\Utility\Unicode\TextIterator::SENTENCE);
		$iterator->rewind();
		$this->assertFalse($iterator->isBoundary(), 'This element was a boundary element.');

		$iterator->next();
		$this->assertTrue($iterator->isBoundary(), 'This element was no boundary element.');
	}

	/**
	 * Checks if the "isBoundary" method basically works with line iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isBoundaryInLineIterationBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator("This is a test string. \nLet\'s iterate \nit by line", \F3\FLOW3\Utility\Unicode\TextIterator::LINE);
		$iterator->rewind();
		$this->assertFalse($iterator->isBoundary(), 'This element was a boundary element.');

		$iterator->next();
		$this->assertTrue($iterator->isBoundary(), 'This element was no boundary element.');
	}

	/**
	 * Checks if the "following" method basically works with word iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function followingBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);

		$this->assertEquals($iterator->following(11), 14, 'Wrong offset for the following element returned.');
	}

	/**
	 * Checks if the "preceding" method basically works with word iteration
	 *
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function precedingBasicallyWorks() {
		$iterator = new \F3\FLOW3\Utility\Unicode\TextIterator('This is a test string. Let\'s iterate it by word', \F3\FLOW3\Utility\Unicode\TextIterator::WORD);

		$this->assertEquals($iterator->preceding(11), 10, 'Wrong offset for the preceding element returned.' . $iterator->preceding(11));
	}
}

?>