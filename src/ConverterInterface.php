<?php

namespace Jaddek\Serializer
{

    /**
     * Interface ConverterInterface
     * @package Serializer
     */
    interface ConverterInterface
    {
        /**
         * @param string $key
         * @return string
         */
        public function convert(string $key): string;
    }
}