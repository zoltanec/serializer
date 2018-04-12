<?php

class Address
{
    private $address;

    private $house;

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getHouse(): House
    {
        return $this->house;
    }

    /**
     * @param mixed $house
     */
    public function setHouse(House $house): void
    {
        $this->house = $house;
    }
}
