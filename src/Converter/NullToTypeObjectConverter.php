<?php

namespace Zoltanec\Serializer\Converter {

    use Zoltanec\Serializer\ConverterInterface;

    /**
     *
     */
    class NullToTypeObjectConverter implements ConverterInterface
    {
        /**
         * @param $key
         * @param \ReflectionNamedType|null $type
         * @return mixed|string
         */
        public function convert($key, ?\ReflectionNamedType $type = null)
        {
            if (is_null($key) && $type && class_exists($type->getName())) {
                $class = $type->getName();

                return new $class;
            }

            return $key;
        }
    }
}
