<?php

namespace ChameleonSystem\ShopBundle\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChameleonValidatorService
{
    public function __construct
    (
        private readonly ValidatorInterface $validator
    )
    {
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}