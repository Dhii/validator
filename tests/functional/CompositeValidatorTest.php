<?php

namespace Dhii\Validator\Test\Func;

use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use Dhii\Validator\CallbackValidator;
use Dhii\Validator\CompositeValidator as Subject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class CompositeValidatorTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @param array $validators The validators of the composite validator.
     * @return Subject&MockObject The new subject instance.
     */
    protected function createSubject(array $validators = []): Subject
    {
        $mock = $this->getMockBuilder(Subject::class)
            ->setMethods(null)
            ->setConstructorArgs([$validators])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new validator instance.
     *
     * @param callable $function The function that will do the validation.
     *                           If non-empty string returned, validation fails.
     * @return ValidatorInterface&MockObject The validator.
     */
    protected function createValidator(callable $function): ValidatorInterface
    {
        $mock = $this->getMockBuilder(CallbackValidator::class)
            ->setConstructorArgs([$function])
            ->setMethods(null)
            ->getMock();

        return $mock;
    }

    /**
     * Provides various testing configurations for validation.
     *
     * @return array[] Values for testing.
     */
    public function valueProvider(): array
    {
        return [
            [
                new stdClass(),
                10,
                ['Value is not numeric', 'Value is not greater than 10'],
            ],
            [
                7,
                10,
                ['Value is not greater than 10'],
            ],
            [
                0,
                10,
                ['Value is invalid: int(0)', 'Value is not greater than 10']
            ],
            [
                11,
                10,
                [],
            ]
        ];
    }

    /**
     * Tests validation scenarios
     *
     * @dataProvider valueProvider
     * @param mixed $value The value to validate.
     * @param float $limit The lower limit for "greater than" validation.
     * @param array $exceptionMessages A list of exception messages to expect. Empty to expect none.
     */
    public function testValidate($value, float $limit, array $exceptionMessages)
    {
        {
            $notFalsy = $this->createValidator(function ($value) {
                if ($value == false) {
                    return false;
                }
            });
            $isNumeric = $this->createValidator(
                function ($value) {
                    if (!is_numeric($value)) {
                        return 'Value is not numeric';
                    }
                }
            );
            $greaterThan = $this->createValidator(
                function ($value) use ($limit) {
                    if (!(is_scalar($value) && $value > $limit)) {
                        return "Value is not greater than $limit";
                    }
                }
            );
            $subject = $this->createSubject([$notFalsy, $isNumeric, $greaterThan]);
        }

        {
            if (!count($exceptionMessages)) {
                $this->expectNotToPerformAssertions();
            }

            try {
                $subject->validate($value);
            } catch (ValidationFailedExceptionInterface $e) {
                $errors = [];
                array_push($errors, ...$e->getValidationErrors()); // Normalize iterable to array
                $this->assertEquals(sprintf('Validation failed with %1$d errors', count($exceptionMessages)), $e->getMessage());
                for ($i = 0; $i < count($exceptionMessages); $i++) {
                    $this->assertArrayHasKey($i, $errors, sprintf('Expected error message not present: %1$s', $exceptionMessages[$i]));
                    $this->assertEquals($exceptionMessages[$i], $errors[$i]->getMessage());
                }
                $this->assertEquals(count($exceptionMessages), count($errors));
                $this->assertSame($value, $e->getValidationSubject());
                $this->assertSame($subject, $e->getValidator());
            }
        }
    }
}
