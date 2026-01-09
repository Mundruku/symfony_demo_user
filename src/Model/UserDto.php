<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\UserStatus;

class UserDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'User name is required.')]
        #[Assert\Length(min: 2,  minMessage: 'The user name must be at least {{ limit }} characters long.')]
        public string $name,

        #[Assert\NotNull(message: 'Email is required.')]
        #[Assert\Email(
            message: 'The email {{ value }} is not a valid email.',
        )]
        public string $email,

    
        public UserStatus $status,
    ) {}
}
