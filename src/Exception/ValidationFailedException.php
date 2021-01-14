<?php
declare(strict_types=1);

namespace Dhii\Validator\Exception;

use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use RuntimeException;
use Throwable;

/**
 * Signals that validation has failed.
 */
class ValidationFailedException
    extends RuntimeException
    implements ValidationFailedExceptionInterface
{
    /** @var ValidatorInterface */
    protected $validator;
    /** @var mixed */
    protected $subject;
    /** @var iterable */
    protected $errors;

    public function __construct(
        ValidatorInterface $validator,
        $subject,
        iterable $errors,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->validator = $validator;
        $this->subject = $subject;
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @inheritDoc
     */
    public function getValidationErrors(): iterable
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function getValidationSubject()
    {
        return $this->subject;
    }
}
