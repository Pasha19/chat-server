<?php

declare(strict_types=1);

namespace App\Data;

class User
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $uid;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     *
     * @return User
     */
    public function setUid(string $uid): User
    {
        $this->uid = $uid;

        return $this;
    }
}
