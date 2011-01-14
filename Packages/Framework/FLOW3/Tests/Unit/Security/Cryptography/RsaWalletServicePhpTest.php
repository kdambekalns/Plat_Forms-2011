<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\Security\Cryptography;

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
 * Testcase for for the PHP (OpenSSL) based RSAWalletService
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class RsaWalletServicePhpTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * Set up this testcase.
	 * In this case this only marks the test to be skipped if openssl extension is not installed
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setUp() {
		if (!function_exists('openssl_pkey_new')) {
			$this->markTestSkipped('openssl_pkey_new() not available');
		} else {
			$objectManagerCallback = function() {
				return new \F3\FLOW3\Security\Cryptography\OpenSslRsaKey(func_get_arg(1), func_get_arg(2));
			};
			$mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
			$mockObjectManager->expects($this->any())->method('create')->will($this->returnCallback($objectManagerCallback));

			$currentKeys = array();
			$setCallBack = function() use (&$currentKeys) {
				$args = func_get_args();
				$currentKeys[$args[0]] = $args[1];
			};
			$getCallBack = function() use (&$currentKeys) {
				$args = func_get_args();
				return $currentKeys[$args[0]];
			};
			$hasCallBack = function() use (&$currentKeys) {
				$args = func_get_args();
				return isset($currentKeys[$args[0]]);
			};
			$mockCache = $this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
			$mockCache->expects($this->any())->method('set')->will($this->returnCallback($setCallBack));
			$mockCache->expects($this->any())->method('get')->will($this->returnCallback($getCallBack));
			$mockCache->expects($this->any())->method('has')->will($this->returnCallback($hasCallBack));

			$this->rsaWalletService = new \F3\FLOW3\Security\Cryptography\RsaWalletServicePhp();
			$this->rsaWalletService->injectObjectManager($mockObjectManager);
			$this->rsaWalletService->injectKeystoreCache($mockCache);

			$this->keyPairUuid = $this->rsaWalletService->generateNewKeypair();
		}
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function encryptingAndDecryptingBasicallyWorks() {
		$plaintext = 'some very sensitive data!';
		$ciphertext = $this->rsaWalletService->encryptWithPublicKey($plaintext, $this->keyPairUuid);

		$this->assertNotEquals($ciphertext, $plaintext);
		$this->assertEquals($plaintext, $this->rsaWalletService->decrypt($ciphertext, $this->keyPairUuid));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function checkRSAEncryptedPasswordReturnsTrueForACorrectPassword() {
		$encryptedPassword = $this->rsaWalletService->encryptWithPublicKey('password', $this->keyPairUuid);

		$passwordHash = 'af1e8a52451786a6b3bf78838e03a0a2';
		$salt = 'a709157e66e0197cafa0c2ba99f6e252';

		$this->assertTrue($this->rsaWalletService->checkRSAEncryptedPassword($encryptedPassword, $passwordHash, $salt, $this->keyPairUuid));
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function checkRSAEncryptedPasswordReturnsFalseForAnIncorrectPassword() {
		$encryptedPassword = $this->rsaWalletService->encryptWithPublicKey('wrong password', $this->keyPairUuid);

		$passwordHash = 'af1e8a52451786a6b3bf78838e03a0a2';
		$salt = 'a709157e66e0197cafa0c2ba99f6e252';

		$this->assertFalse($this->rsaWalletService->checkRSAEncryptedPassword($encryptedPassword, $passwordHash, $salt, $this->keyPairUuid));
	}

	/**
	 * @test
	 * @category unit
	 * @expectedException \F3\FLOW3\Security\Exception\DecryptionNotAllowedException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function decryptingWithAKeypairUUIDMarkedForPasswordUsageThrowsAnException() {
		$this->keyPairUuid = $this->rsaWalletService->generateNewKeypair(TRUE);
		$this->rsaWalletService->decrypt('some cipher', $this->keyPairUuid);
	}
}
?>