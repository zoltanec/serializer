<?php

namespace Jaddek\Serializer\Converter {

    use Jaddek\Serializer\ConverterInterface;

    /**
     *
     */
    class CamelCaseToUnderScoreConverter implements ConverterInterface
    {
        /**
         * @param string $key
         * @return string
         */
        public function convert($key)
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