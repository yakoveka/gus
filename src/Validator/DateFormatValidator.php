<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DateFormatValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DateFormat) {
            throw new UnexpectedTypeException($constraint, DateFormat::class);
        }

        if (null === $value || '' === $value) {
            // Allow other constraints like NotBlank to handle empty values
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Create a DateTime object from the input value
        $date = \DateTime::createFromFormat($constraint->format, $value);

        // Check if date creation was successful and the formatted date matches the input
        if (!$date || $date->format($constraint->format) !== $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}

