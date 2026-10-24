<?php

declare(strict_types=1);

/**
 * AFRT Core Standalone Test Runner
 * Executes the complete test suite without external dependencies.
 */

require_once __DIR__ . '/../src/autoload.php';

// Polyfill minimal PHPUnit assertions if PHPUnit is not loaded
if (!class_exists('PHPUnit\\Framework\\TestCase')) {
    abstract class StandaloneTestCase
    {
        public static int $assertions = 0;

        protected function setUp(): void
        {
        }

        protected function assertSame($expected, $actual, string $message = ''): void
        {
            self::$assertions++;
            if ($expected !== $actual) {
                throw new \AssertionError($message ?: sprintf("Failed asserting that %s matches expected %s.", var_export($actual, true), var_export($expected, true)));
            }
        }

        protected function assertTrue($actual, string $message = ''): void
        {
            self::$assertions++;
            if ($actual !== true) {
                throw new \AssertionError($message ?: "Failed asserting that value is true.");
            }
        }

        protected function assertFalse($actual, string $message = ''): void
        {
            self::$assertions++;
            if ($actual !== false) {
                throw new \AssertionError($message ?: "Failed asserting that value is false.");
            }
        }

        protected function assertNull($actual, string $message = ''): void
        {
            self::$assertions++;
            if ($actual !== null) {
                throw new \AssertionError($message ?: "Failed asserting that value is null.");
            }
        }

        protected function assertEmpty($actual, string $message = ''): void
        {
            self::$assertions++;
            if (!empty($actual)) {
                throw new \AssertionError($message ?: "Failed asserting that value is empty.");
            }
        }

        protected function assertNotEmpty($actual, string $message = ''): void
        {
            self::$assertions++;
            if (empty($actual)) {
                throw new \AssertionError($message ?: "Failed asserting that value is not empty.");
            }
        }

        protected function assertCount(int $expectedCount, $actual, string $message = ''): void
        {
            self::$assertions++;
            $count = is_countable($actual) ? count($actual) : 0;
            if ($count !== $expectedCount) {
                throw new \AssertionError($message ?: sprintf("Failed asserting that count %d matches expected %d.", $count, $expectedCount));
            }
        }

        protected function assertGreaterThan($expected, $actual, string $message = ''): void
        {
            self::$assertions++;
            if (!($actual > $expected)) {
                throw new \AssertionError($message ?: sprintf("Failed asserting that %s is greater than %s.", var_export($actual, true), var_export($expected, true)));
            }
        }

        protected function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
        {
            self::$assertions++;
            if (!($actual >= $expected)) {
                throw new \AssertionError($message ?: sprintf("Failed asserting that %s is >= %s.", var_export($actual, true), var_export($expected, true)));
            }
        }

        protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
        {
            self::$assertions++;
            if (strpos($haystack, $needle) === false) {
                throw new \AssertionError($message ?: sprintf("Failed asserting that '%s' contains '%s'.", $haystack, $needle));
            }
        }

        protected ?string $expectedException = null;

        protected function expectException(string $exceptionClass): void
        {
            $this->expectedException = $exceptionClass;
        }

        public function getExpectedException(): ?string
        {
            return $this->expectedException;
        }
    }

    class_alias(StandaloneTestCase::class, 'PHPUnit\\Framework\\TestCase');
}

// Include all test classes
require_once __DIR__ . '/Fixtures/SyntheticDepartmentFactory.php';
require_once __DIR__ . '/Unit/WorkloadCalculatorTest.php';
require_once __DIR__ . '/Unit/CohortStreamBundlerTest.php';
require_once __DIR__ . '/Unit/WorkloadValidatorTest.php';
require_once __DIR__ . '/Unit/SubjectDiscrepancyMatcherTest.php';
require_once __DIR__ . '/Integration/DepartmentWorkflowTest.php';

$testClasses = [
    Afrt\Core\Tests\Unit\WorkloadCalculatorTest::class,
    Afrt\Core\Tests\Unit\CohortStreamBundlerTest::class,
    Afrt\Core\Tests\Unit\WorkloadValidatorTest::class,
    Afrt\Core\Tests\Unit\SubjectDiscrepancyMatcherTest::class,
    Afrt\Core\Tests\Integration\DepartmentWorkflowTest::class,
];

$startTime = microtime(true);
$startMemory = memory_get_usage();

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$failures = [];

echo "=================================================================\n";
echo " AFRT Core - Standalone Test Suite Runner\n";
echo " PHP Version: " . PHP_VERSION . " (" . PHP_SAPI . ")\n";
echo " Time: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================================\n\n";

foreach ($testClasses as $className) {
    $ref = new ReflectionClass($className);
    $shortName = $ref->getShortName();
    echo "Running {$shortName}...\n";

    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        if (!str_starts_with($method->getName(), 'test')) {
            continue;
        }

        $totalTests++;
        /** @var \PHPUnit\Framework\TestCase $testInstance */
        $testInstance = new $className();

        // Call setUp if present
        $setUpMethod = $ref->getMethod('setUp');
        $setUpMethod->setAccessible(true);
        $setUpMethod->invoke($testInstance);

        $caughtException = null;

        try {
            $method->invoke($testInstance);
        } catch (Throwable $e) {
            $caughtException = $e;
        }

        // Check expected exception
        $expected = method_exists($testInstance, 'getExpectedException')
            ? $testInstance->getExpectedException()
            : null;

        $testPassed = false;
        if ($expected !== null) {
            if ($caughtException !== null && $caughtException instanceof $expected) {
                $testPassed = true;
            } else {
                $msg = $caughtException
                    ? "Expected {$expected}, but caught " . get_class($caughtException) . ": " . $caughtException->getMessage()
                    : "Expected exception {$expected}, but none was thrown.";
                $failures[] = "{$shortName}::{$method->getName()}: {$msg}";
            }
        } else {
            if ($caughtException === null) {
                $testPassed = true;
            } else {
                $failures[] = "{$shortName}::{$method->getName()}: " . $caughtException->getMessage() . " at " . $caughtException->getFile() . ":" . $caughtException->getLine();
            }
        }

        if ($testPassed) {
            $passedTests++;
            echo "  [PASS] {$method->getName()}\n";
        } else {
            $failedTests++;
            echo "  [FAIL] {$method->getName()}\n";
        }
    }
    echo "\n";
}

$elapsedTime = microtime(true) - $startTime;
$peakMemory = memory_get_peak_usage() - $startMemory;

echo "=================================================================\n";
echo " TEST SUMMARY\n";
echo "=================================================================\n";
echo " Total Tests:      {$totalTests}\n";
echo " Passed:           {$passedTests}\n";
echo " Failed:           {$failedTests}\n";
echo " Execution Time:   " . round($elapsedTime * 1000, 2) . " ms (" . round($elapsedTime, 4) . " s)\n";
echo " Peak Memory:      " . round($peakMemory / 1024, 2) . " KB\n";

if ($failedTests > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
} else {
    echo "\nALL TESTS PASSED SUCCESSFULLY! (100% Pass Rate)\n";
    exit(0);
}
