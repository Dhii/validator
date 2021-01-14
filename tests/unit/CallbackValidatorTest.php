<?php

namespace Dhii\Validator\Test\Unit;

use Dhii\Validator\VarDumpTrait as Subject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CallbackValidatorTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @return MockObject The new instance.
     */
    protected function createSubject(): MockObject
    {
        $mock = $this->getMockBuilder(Subject::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }

    /**
     * @return list<list<string>> For each element in 1st list, first arg is dump, second is expectation.
     */
    public function provideVarDumpParams(): array
    {
        return [
            // Just the normal dump of an integer
            [
                'int(123)',
                'int(123)'
            ],
            // A dump of an integer, contaminated by xDebug
            [
                '/home/runner/work/validator/validator/src/CallbackValidator.php:67:
int(123)',
                'int(123)'
            ],
            // A normal dump of an object
            [
                'object(stdClass)#1 (2) {
  ["asd"]=>
  string(5) "hello"
  ["qwe"]=>
  string(5) "world"
}',
                'object(stdClass)#1 (2) {
  ["asd"]=>
  string(5) "hello"
  ["qwe"]=>
  string(5) "world"
}'
            ],
        ];
    }

    /**
     * @dataProvider provideVarDumpParams
     * @param string $dump The dump to clean.
     * @param string $expectation What the cleaned dump should look like.
     */
    public function testCleanVarDump(string $dump, string $expectation)
    {
        {
            $subject = $this->createSubject();
        }

        {
            // Testing protected method
            $class = new ReflectionClass($subject);
            $method = $class->getMethod('cleanVarDump');
            $method->setAccessible(true);
            $result = $method->invoke($subject, $dump);

            $this->assertEquals($expectation, $result);
        }
    }
}
