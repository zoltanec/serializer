<?php

class Profile
{
    private $name;
    private $surnameIn;
    private $address;

    /**
     * @return mixed
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSurnameIn()
    {
        return $this->surnameIn;
    }

    /**
     * @param mixed $surnameIn
     */
    public function setSurnameIn($surnameIn): void
    {
        $this->surnameIn = $surnameIn;
    }

    /**
     * @return mixed
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }
}