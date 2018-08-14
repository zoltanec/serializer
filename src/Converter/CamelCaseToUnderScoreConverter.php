<?php

namespace Zoltanec\Serializer\Converter {

    use Zoltanec\Serializer\ConverterInterface;

    /**
     *
     */
    class CamelCaseToUnderScoreConverter implements ConverterInterface
    {
        /**
         * @param $key
         * @param \ReflectionNamedType|null $type
         * @return mixed|string
         */
        public function convert($key, ?\ReflectionNamedType $type = null)
        {
            return is_string($key) ? $this->toUnderscoreString($key) : $key;
        }

        /**
         * @param string $string
         *
         * @return null|string|string[]
         */
        private function toUnderscoreString(string $string): string
        {
            return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
        }
    }
}