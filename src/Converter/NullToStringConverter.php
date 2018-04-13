<?php

namespace Jaddek\Serializer\Converter
{
    use Jaddek\Serializer\ConverterInterface;

    /**
     *
     */
    class NullToStringConverter implements ConverterInterface
    {
        /**
         * @param string $key
         * @return string
         */
        public function convert(string $key): string
        {
            return is_null($key) ? '' : $key;
        }
    }
}