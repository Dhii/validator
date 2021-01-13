<?php
declare(strict_types=1);

namespace Dhii\Validator\Exception;

use Dhii\Validation\Exception\ValidationExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use RuntimeException;
use Throwable;

/**
 * A problem that occurs during validation.
 */
class ValidationException extends RuntimeException implements ValidationExceptionInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    public function __construct(
        ValidatorInterface $validator,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->validator = $validator;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritDoc
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
