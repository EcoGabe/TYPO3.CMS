<?php
namespace TYPO3\CMS\Core\Tests\Unit\Core;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase
 */
class SystemEnvironmentBuilderTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder|\TYPO3\TestingFramework\Core\AccessibleObjectInterface
     */
    protected $subject;

    /**
     * Set up testcase
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getAccessibleMock(\TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::class, ['dummy']);
    }

    /**
     * Data provider for 'fileDenyPatternMatchesPhpExtension' test case.
     *
     * @return array
     */
    public function fileDenyPatternMatchesPhpExtensionDataProvider()
    {
        $fileName = $this->getUniqueId('filename');
        $data = [];
        $phpExtensions = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', 'php,php3,php4,php5,php6,phpsh,phtml,pht', true);
        foreach ($phpExtensions as $extension) {
            $data[] = [$fileName . '.' . $extension];
            $data[] = [$fileName . '.' . $extension . '.txt'];
        }
        return $data;
    }

    /**
     * Tests whether an accordant PHP extension is denied.
     *
     * @test
     * @dataProvider fileDenyPatternMatchesPhpExtensionDataProvider
     * @param string $phpExtension
     */
    public function fileDenyPatternMatchesPhpExtension($phpExtension)
    {
        $this->assertGreaterThan(0, preg_match('/' . FILE_DENY_PATTERN_DEFAULT . '/', $phpExtension), $phpExtension);
    }

    /**
     * @test
     */
    public function getPathThisScriptCliReadsLocalPartFromArgv()
    {
        $fakedLocalPart = $this->getUniqueId('Test');
        $GLOBALS['_SERVER']['argv'][0] = $fakedLocalPart;
        $this->assertStringEndsWith($fakedLocalPart, $this->subject->_call('getPathThisScriptCli'));
    }

    /**
     * @test
     */
    public function getPathThisScriptCliReadsLocalPartFromEnv()
    {
        $fakedLocalPart = $this->getUniqueId('Test');
        unset($GLOBALS['_SERVER']['argv']);
        $GLOBALS['_ENV']['_'] = $fakedLocalPart;
        $this->assertStringEndsWith($fakedLocalPart, $this->subject->_call('getPathThisScriptCli'));
    }

    /**
     * @test
     */
    public function getPathThisScriptCliReadsLocalPartFromServer()
    {
        $fakedLocalPart = $this->getUniqueId('Test');
        unset($GLOBALS['_SERVER']['argv']);
        unset($GLOBALS['_ENV']['_']);
        $GLOBALS['_SERVER']['_'] = $fakedLocalPart;
        $this->assertStringEndsWith($fakedLocalPart, $this->subject->_call('getPathThisScriptCli'));
    }

    /**
     * @test
     */
    public function getPathThisScriptCliAddsCurrentWorkingDirectoryFromServerEnvironmentToLocalPathOnUnix()
    {
        $GLOBALS['_SERVER']['argv'][0] = 'foo';
        $fakedAbsolutePart = '/' . $this->getUniqueId('Absolute') . '/';
        $_SERVER['PWD'] = $fakedAbsolutePart;
        $this->assertStringStartsWith($fakedAbsolutePart, $this->subject->_call('getPathThisScriptCli'));
    }

    /**
     * @test
     */
    public function initializeGlobalVariablesSetsGlobalT3ServicesArray()
    {
        unset($GLOBALS['T3_SERVICES']);
        $this->subject->_call('initializeGlobalVariables');
        $this->assertIsArray($GLOBALS['T3_SERVICES']);
    }

    /**
     * Data provider for initializeGlobalTimeTrackingVariablesSetsGlobalVariables
     *
     * @return array
     */
    public function initializeGlobalTimeTrackingVariablesSetsGlobalVariablesDataProvider()
    {
        return [
            'EXEC_TIME' => ['EXEC_TIME'],
            'ACCESS_TIME' => ['ACCESS_TIME'],
            'SIM_EXEC_TIME' => ['SIM_EXEC_TIME'],
            'SIM_ACCESS_TIME' => ['SIM_ACCESS_TIME']
        ];
    }

    /**
     * @test
     * @dataProvider initializeGlobalTimeTrackingVariablesSetsGlobalVariablesDataProvider
     * @param string $variable Variable to check for in $GLOBALS
     */
    public function initializeGlobalTimeTrackingVariablesSetsGlobalVariables($variable)
    {
        unset($GLOBALS[$variable]);
        $this->subject->_call('initializeGlobalTimeTrackingVariables');
        $this->assertTrue(isset($GLOBALS[$variable]));
    }

    /**
     * @test
     */
    public function initializeGlobalTimeTrackingVariablesRoundsAccessTimeToSixtySeconds()
    {
        $this->subject->_call('initializeGlobalTimeTrackingVariables');
        $this->assertEquals(0, $GLOBALS['ACCESS_TIME'] % 60);
    }

    /**
     * @test
     */
    public function initializeGlobalTimeTrackingVariablesRoundsSimAccessTimeToSixtySeconds()
    {
        $this->subject->_call('initializeGlobalTimeTrackingVariables');
        $this->assertEquals(0, $GLOBALS['SIM_ACCESS_TIME'] % 60);
    }

    /**
     * @test
     */
    public function initializeBasicErrorReportingExcludesStrict()
    {
        $backupReporting = error_reporting();
        $this->subject->_call('initializeBasicErrorReporting');
        $actualReporting = error_reporting();
        error_reporting($backupReporting);
        $this->assertEquals(0, $actualReporting & E_STRICT);
    }

    /**
     * @test
     */
    public function initializeBasicErrorReportingExcludesNotice()
    {
        $backupReporting = error_reporting();
        $this->subject->_call('initializeBasicErrorReporting');
        $actualReporting = error_reporting();
        error_reporting($backupReporting);
        $this->assertEquals(0, $actualReporting & E_NOTICE);
    }

    /**
     * @test
     */
    public function initializeBasicErrorReportingExcludesDeprecated()
    {
        $backupReporting = error_reporting();
        $this->subject->_call('initializeBasicErrorReporting');
        $actualReporting = error_reporting();
        error_reporting($backupReporting);
        $this->assertEquals(0, $actualReporting & E_DEPRECATED);
    }
}
