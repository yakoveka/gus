<?php

// src/Validator/DateFormat.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class DateFormat extends Constraint
{
    public string $format;
    public string $message;

    public function __construct(
        string $format = 'Y-m-d',
        string $message = 'The date "{{ string }}" is not in the format yyyy-mm-dd.',
        array $groups = null,
        mixed $payload = null
    ) {
        // Do not pass $message to the parent constructor
        parent::__construct(groups: $groups, payload: $payload);

        $this->format = $format;
        $this->message = $message;
    }
}

