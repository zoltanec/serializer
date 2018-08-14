<?php

namespace Zoltanec\Serializer {

    /**
     * Interface ConverterInterface
     * @package Serializer
     */
    interface ConverterInterface
    {
        /**
         * @param $key
         * @param \ReflectionNamedType|null $type
         * @return mixed
         */
        public function convert($key, \ReflectionNamedType $type = null);
    }
}