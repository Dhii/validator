<?php
declare(strict_types=1);

namespace Dhii\Validator;

use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use Dhii\Validator\Exception\ValidationException;
use Dhii\Validator\Exception\ValidationFailedException;
use Exception;
use RuntimeException;

class CallbackValidator implements ValidatorInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param callable $callback The validating callback.
     *                           Will receive the value to be validated.
     *                           Return a string to signal validation failure, providing the reason.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        try {
            $callback = $this->callback;
            $result = $callback($value);
        } catch (Exception $e) {
            if ($e instanceof ValidationFailedExceptionInterface) {
                throw $e;
            }

            throw new ValidationException($this, sprintf('Failed validating %1$s', $this->varDump($value)), 0, $e);
        }

        if ($result === null) {
            return;
        }

        $message = $result === false
            ? sprintf('Value is invalid')
            : (string) $result;
        throw new ValidationFailedException($this, $value, [$message], $message);
    }

    /**
     * Retrieves the dump of a variable as a string.
     *
     * @see var_dump()
     * @param mixed $value The value to get the dump of.
     * @return string The dump.
     * @throws RuntimeException If problem retrieving.
     */
    protected function varDump($value): string
    {
        ob_start();
        var_dump($value);
        $dump = ob_get_clean();

        if ($dump === false) {
            throw new RuntimeException('Output buffering not started');
        }

        return $dump;
    }
}
