<?php
declare(ENCODING = 'utf-8');
namespace F3\Testing;

/*                                                                        *
 * This script belongs to the FLOW3 package "Testing".                    *
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

require_once('PHPUnit/Autoload.php');
require_once('vfsStream/vfsStream.php');
require_once('PHP/CodeCoverage/Report/HTML.php');
require_once(FLOW3_PATH_FLOW3 . 'Tests/BaseTestCase.php');
require_once(FLOW3_PATH_FLOW3 . 'Tests/UnitTestCase.php');
require_once(FLOW3_PATH_FLOW3 . 'Tests/LegacyBaseTestCase.php');

/**
 * A preliminary test runner for TYPO3s unit tests
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class TestRunnerWeb {

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	public static $objectManagerForTesting;

	/**
	 * @var \PHPUnit_Framework_TestSuite
	 */
	protected $testSuite;

	/**
	 * @var string
	 */
	protected $packagesPath;

	/**
	 * @var array
	 */
	protected $testBlacklist = array('PHPUnit');

	/**
	 * @var string
	 */
	protected $packageKey = '*';

	/**
	 * @var string
	 */
	protected $testcaseClassName = '';

	/**
	 * @var string
	 */
	protected $testOutputPath = './';

	/**
	 * @var string
	 */
	protected $coverageOutputPath = './coverage/';

	/**
	 * @var boolean
	 */
	protected $collectCodeCoverage = FALSE;

	/**
	 * @var \F3\FLOW3\MVC\RequestInterface: Yeah, this is quick and dirty and really preliminiary!
	 */
	public $request;

	/**
	 * Constructor
	 *
	 * @param  \F3\FLOW3\Object\ObjectManagerInterface $objectManager A reference to the object manager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Setter for the package key of the package to test, * means all.
	 *
	 * @param string $packageKey
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setPackageKey($packageKey) {
		$this->packageKey = $packageKey;
	}

	/**
	 * Setter for the testcase class name, empty means all.
	 *
	 * @param string $testcaseClassName
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setTestcaseClassName($testcaseClassName) {
		$this->testcaseClassName = $testcaseClassName;
	}

	/**
	 * Setter for where the test output should go
	 *
	 * @param string $outputPath
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setTestOutputPath($outputPath) {
		$this->testOutputPath = $outputPath;
	}

	/**
	 * Setter for where the coverage output should go
	 *
	 * @param string $path
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setCoverageOutputPath($path) {
		if (!is_dir($path)) {
			\F3\FLOW3\Utility\Files::createDirectoryRecursively($path);
		}
		$this->coverageOutputPath = $path;
	}

	/**
	 * Enables collection of code coverage during test run
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function enableCodeCoverage() {
		$this->collectCodeCoverage = TRUE;
		#$this->initializePHPUnitFilter();
	}

	/**
	 * Adds the FLOW3 root and everything in the include_path to the PHPUnit filter,
	 * so it won't be considered when collecting code coverage data.
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function initializePHPUnitFilter() {
		\PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(FLOW3_PATH_ROOT);
		foreach(explode(':', ini_get('include_path')) as $include_path) {
			if(is_dir($include_path)) {
				\PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist($include_path);
			}
		}
	}

	/**
	 * Traverses the Tests directory of the given package and returns an
	 * array of filenames (including path) of all files ending with "Test.php".
	 *
	 * @return array Filenames of all found testcase files
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function getTestcaseFilenames() {
		$packageManager = $this->objectManager->get('F3\FLOW3\Package\PackageManagerInterface');
		$packages = array();
		$testcaseFilenames = array();

		$testcaseClassNameMatches = array();
		preg_match('/F3\\\\([^\\\\]*)\\\\(.*)/', $this->testcaseClassName, $testcaseClassNameMatches);
		if (count($testcaseClassNameMatches) === 3) {
			$this->testcaseClassName = $testcaseClassNameMatches[2];
			if ($testcaseClassNameMatches[1] === '.*') {
				$packages = $packageManager->getActivePackages();
			} elseif ($packageManager->isPackageActive($testcaseClassNameMatches[1])) {
				$packages = array($packageManager->getPackage($testcaseClassNameMatches[1]));
			}
		} elseif ($this->packageKey == '*') {
			$packages = $packageManager->getActivePackages();
			$this->testcaseClassName = '.*Test';
		} elseif ($packageManager->isPackageActive($this->packageKey)) {
			$packages = array($packageManager->getPackage($this->packageKey));
			$this->testcaseClassName = '.*Test';
		}

		shuffle($packages);
		foreach ($packages as $package) {
			if (in_array($package->getPackageKey(), $this->testBlacklist)) {
				continue;
			}
			$testPath = $package->getPackagePath() . 'Tests/Unit/';
			if (is_dir($testPath)) {
				try {
					$testsDirectoryIterator = new \RecursiveDirectoryIterator($testPath);

					$testcaseFilenames = $this->readDirectories($testcaseFilenames, $testsDirectoryIterator);
					\PHP_CodeCoverage_Filter::getInstance()->addDirectoryToWhitelist($package->getPackagePath() . 'Classes');
				} catch(\Exception $exception) {
					throw new \F3\Testing\Exception($exception->getMessage(), 1170236926);
				}
			}
			shuffle($testcaseFilenames);
		}
		return $testcaseFilenames;
	}

	/**
	 * Reads all test files from base directory and subdirecotries
	 *
	 * @param array $testcaseFilenames array to store found testcases
	 * @param object $testsDirectoryIterator RecursiveDirectoryIterator object
	 * @return array Filenames of all found testcase files
	 * @author Ronny Unger <ru@php-workx.de>
	 */
	protected function readDirectories(array $testcaseFilenames, $testsDirectoryIterator) {
		while ($testsDirectoryIterator->valid()) {
			if ($testsDirectoryIterator->hasChildren() && $testsDirectoryIterator->getFilename() != '.svn') {
				$testcaseFilenames = $this->readDirectories($testcaseFilenames, $testsDirectoryIterator->getChildren());
			}
			if (!$testsDirectoryIterator->isDir()) {
				$pathAndFilename = \F3\FLOW3\Utility\Files::getUnixStylePath($testsDirectoryIterator->getPathname());
				if (preg_match('/\/' . str_replace('\\', '\\/', $this->testcaseClassName) . '\.php/', $pathAndFilename) === 1) {
					$testcaseFilenames[] = $pathAndFilename;
				}
			}
			$testsDirectoryIterator->next();
		}

		return $testcaseFilenames;
	}

	/**
	 * require() each of the testcase files.
	 *
	 * @param array $testcaseFileNamesAndPaths
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function requireTestCaseFiles(array $testcaseFileNamesAndPaths) {
		foreach ($testcaseFileNamesAndPaths as $filenameAndPath) {
			require($filenameAndPath);
		}
	}

	/**
	 * Main function - runs the tests and outputs HTML code
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @internal Preliminary solution - there surely will be nicer ways to implement a test runner
	 */
	public function run() {
		$this->renderPageHeader();
		$this->renderTestForm();

		if (!empty($this->packageKey)) {
			$testcaseFileNamesAndPaths = $this->getTestcaseFilenames();

			if (count($testcaseFileNamesAndPaths) > 0) {
				$this->renderInfoAndProgressbar();

				$this->requireTestCaseFiles($testcaseFileNamesAndPaths);

				$testListener = new \F3\Testing\TestListener;
				$testListener->baseUri = $this->request->getBaseUri();

				$testResult = new \PHPUnit_Framework_TestResult;
				$testResult->addListener($testListener);
				$testResult->collectCodeCoverageInformation($this->collectCodeCoverage);

				$startTime = microtime(TRUE);
				foreach (get_declared_classes() as $className) {
					$namespaceSegments = explode('\\', $className);
					if (substr($className, -4, 4) == 'Test' && count($namespaceSegments) > 4 && $namespaceSegments[2] === 'Tests' && $namespaceSegments[3] === 'Unit') {
						$class = new \ReflectionClass($className);
						if ($class->isSubclassOf('PHPUnit_Framework_TestCase') && substr($className, 0, 8) !== 'PHPUnit_') {
							$testSuite = new \PHPUnit_Framework_TestSuite($class);
							$testSuite->run($testResult);
						}
					}
				}
				$endTime = microtime(TRUE);

					// Display test statistics:
				if ($testResult->wasSuccessful()) {
						echo '<script type="text/javascript">document.getElementById("progress-bar").style.backgroundColor = "green";document.getElementById("progress-bar").style.backgroundImage = "none";</script>
						<h1 class="success">SUCCESS</h1>
						'.$testResult->count().' tests, '.$testResult->failureCount().' failures, '.$testResult->errorCount().' errors.
						</h1>';
				} else {
						echo '
						<script>document.getElementById("progress-bar").style.backgroundColor = "red";document.getElementById("progress-bar").style.backgroundImage = "none";</script>
						<h1 class="failure">FAILURE</h1>
						'.$testResult->count().' tests, '.$testResult->failureCount().' failures, '.$testResult->errorCount().' errors.
					';
				}

				echo '<p>Peak memory usage was: ~' . floor(memory_get_peak_usage()/1024/1024) . ' MByte.<br />';
				echo 'Test run took ' . round(($endTime - $startTime), 4) . ' seconds.</p>';

				if($this->collectCodeCoverage === TRUE) {
					\F3\FLOW3\Utility\Files::emptyDirectoryRecursively($this->coverageOutputPath);
					$report = new \PHP_CodeCoverage_Report_HTML();
					$report->process($testResult->getCodeCoverage(), $this->coverageOutputPath);
					echo '<a href="_Resources/CodeCoverageReport/index.html">See code coverage report...</a>';
				}
			} else {
				echo '<p>No testcase found. Did you specify the intended pattern?</p>';
			}
		}

		$this->renderPageFooter();
	}

	/**
	 * Renders the form shown on top of the test screen
	 *
	 * @return string HTML code for the test selection/submission form
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function renderTestForm() {
		echo '<form action="testing/" id="packageselectorbox" method="post">' . PHP_EOL;

		$this->renderPackageSelectorBox();
		echo '<br /><label title="Xdebug is required in order to enable code coverage analysis"><input type="checkbox" name="collectCodeCoverageData" value="1" ';
		if(function_exists('xdebug_is_enabled') && xdebug_is_enabled()) {
			echo (isset($_REQUEST['collectCodeCoverageData']) && ($_REQUEST['collectCodeCoverageData'])) ? 'checked="checked"' : '';
		} else {
			echo 'disabled="disabled"';
		}
		echo '/> analyze Code Coverage</label><br />';

		echo '<input type="submit" value=" Run " />
			</form>';
	}

	/**
	 * Renders a selector box to chose all or a specific package for which the tests should be run.
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function renderPackageSelectorBox() {
		$options = '';
		$packageManager = $this->objectManager->get('F3\FLOW3\Package\PackageManagerInterface');
		$packages = $packageManager->getAvailablePackages();
		foreach ($packages as $package) {
			if (in_array($package->getPackageKey(), $this->testBlacklist)) {
				continue;
			}

			$selected = '';
			if (isset($_REQUEST['packageToTest']) && $_REQUEST['packageToTest'] == $package->getPackageKey()) {
				$selected = ' selected="selected"';
			}
			$disabled = $packageManager->isPackageActive($package->getPackageKey()) ? '' : ' disabled="disabled"';
			$options .= '<option' . $selected . $disabled . '>' . $package->getPackageKey() . '</option>';
		}

		$testcaseClassName = (isset($_REQUEST['testcaseClassName'])) ? htmlspecialchars($_REQUEST['testcaseClassName']) : '';
		$html = '
				<select size="1" name="packageToTest">
					<option value="*">all packages</option>
					' . $options . '
				</select>
				Filter: <input type="text" name="testcaseClassName" style="width:40em" value="' . $testcaseClassName . '" />' . PHP_EOL;
		echo $html;
	}

	/**
	 * Renders the HTML for the test output page before the actual test output
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function renderPageHeader() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
	<head>
		<base href="' . $this->request->getBaseUri() . '" />
		<title>TYPO3 Testrunner</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			body { font-family:sans-serif; font-size:90%; background-color:#fff; }
			a { color: #000000; }
			#logo { float:right; margin:2ex; }
			#packageselectorbox { clear:both; border-width:1px; border-style:solid none; padding: 1ex 0; }
			h1.success { color:green; }
			h1.failure { color:red; }
			h2.testsuite { font-size:110%; margin:0.4ex; padding-top:0.5ex; }
			div.singletest { margin-left:5ex; }
			div.incomplete strong { color:grey; }
			div.skipped strong { color:grey; }
			div.failure strong { color:red; }
			div.error strong { color:red; }
			div.testsuite { margin-left:0.4em; margin-bottom:1ex; }
			div.testsuiteresults { font-size:90%;margin-left:0.4ex; margin-top:0.5ex }
			div.test img { cursor:pointer; }
			div.testdetail { font-size:90%; border:dashed 1px; padding:1ex; background-color:#eee; }
			div.skipped div.testdetail { display:none; }
			div.testoutput { font-family:monospace; margin-top:1ex; }
		</style>
	</head>
	<body>
		<img src="_Resources/Static/Packages/Testing/Media/f3_logo.gif" id="logo" />
		';
	}

	/**
	 * Renders the HTML for the test output page after the actual test output
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function renderPageFooter() {
		echo '
	</body>
</html>';
	}

	/**
	 * Renders DIVs which contain information and a progressbar to visualize
	 * the running tests. The actual information will be written via JS during
	 * the test runs.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function renderInfoAndProgressbar() {
		echo '
			<br />
			<div style="width:100%; height:12px; border: 1px solid black;">
				<div id="progress-bar" style="float: left; width: 100%; height: 12px; background-repeat:repeat-x; background-image:url(_Resources/Static/Packages/Testing/Media/indicator_green.gif);">&nbsp;</div>
			</div>
			<br />
		';
	}

}

?>
