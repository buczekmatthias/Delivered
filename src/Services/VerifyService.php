<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VerifyService
{
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function verify()
    {
        if ($this->session->get('user')) {
            return true;
        }
        return false;
    }
}
