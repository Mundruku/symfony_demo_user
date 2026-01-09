<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\UserStatus;

class UserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email(
            message: 'The email {{ value }} is not a valid email.',
        )]
        public string $email,

        // Removed dynamic Choice argument (not allowed in attributes); rely on the UserStatus type for validation
        #[Assert\NotBlank]
        public UserStatus $status,
    ) {}
}
