<?php

namespace Zoltanec\Serializer\Converter
{
    use Zoltanec\Serializer\ConverterInterface;

    /**
     *
     */
    class NullToStringConverter implements ConverterInterface
    {
        /**
         * @param $key
         * @param \ReflectionNamedType|null $type
         * @return mixed|string
         */
        public function convert($key, ?\ReflectionNamedType $type = null)
        {
            return is_null($key) ? '' : $key;
        }
    }
}