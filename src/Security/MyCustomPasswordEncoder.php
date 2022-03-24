<?php

namespace App\Security;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

class MyCustomPasswordEncoder extends BasePasswordEncoder
{

private $algorithm;
private $encodeHashAsBase64;
private $iterations;

/**
 * @param string $algorithm          The digest algorithm to use
 * @param bool   $encodeHashAsBase64 Whether to base64 encode the password hash
 * @param int    $iterations         The number of iterations to use to stretch the password hash
 */
public function __construct(string $algorithm = 'sha1', bool $encodeHashAsBase64 = false, int $iterations = 0)
{
    $this->algorithm = $algorithm;
    $this->encodeHashAsBase64 = $encodeHashAsBase64;
    $this->iterations = $iterations;
}

public function encodePassword($raw, $salt="Popol")
{
    if ($this->isPasswordTooLong($raw)) {
        throw new BadCredentialsException('Invalid password.');
    }

    if (!in_array($this->algorithm, hash_algos(), true)) {
        throw new \LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
    }

    $digest = sha1('Popol'.$raw);   

    return $digest;
}

/**
 * {@inheritdoc}
 */
public function isPasswordValid($encoded, $raw, $salt)
{
    return !$this->isPasswordTooLong($raw) && $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
}

    public function needsRehash(string $encoded): bool
    {
       return true;
    }
}