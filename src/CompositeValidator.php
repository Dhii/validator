<?php
declare(strict_types=1);

namespace Dhii\Validator;

use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use Dhii\Validator\Exception\ValidationFailedException;

/**
 * A validator that uses multiple inner validators to validate a subject.
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * @var iterable|ValidatorInterface[]
     */
    protected $validators;

    /**
     * @param iterable|ValidatorInterface[] $validators The inner validators to use when validating.
     */
    public function __construct(iterable $validators)
    {
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            assert($validator instanceof ValidatorInterface);
            try {
                $validator->validate($value);
            } catch (ValidationFailedExceptionInterface $e) {
                $errors[] = $e;
            }
        }

        $errCount = count($errors);
        if ($errCount) {
            throw new ValidationFailedException($this, $value, $errors, $this->__('Validation failed with %1$d errors', [$errCount]));
        }
    }

    /**
     * Translates a string, interpolating params.
     *
     * @param string $string The string to translate. Can be a {@see sprintf()} style format.
     * @param array $params The param values to interpolate into the string.
     * @return string The translated string with params interpolated.
     */
    protected function __(string $string, array $params = []): string
    {
        return vsprintf($string, $params);
    }
}
