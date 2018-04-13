<?php

namespace Jaddek\Serializer\Converter {

    use Jaddek\Serializer\ConverterInterface;

    /**
     *
     */
    class UnderScoreToCamelCaseConverter implements ConverterInterface
    {
        /**
         * @param string $key
         * @return string
         */
        public function convert($key)
        {
            return is_string($key) ? $this->toCamelCaseString($key) : $key;
        }

        /**
         * @param string $string
         * @param string $delimiter
         *
         * @return string
         */
        private function toCamelCaseString(string $string, string $delimiter = '_'): string
        {
            if (mb_strpos($string, $delimiter) !== false) {
                $string = lcfirst(str_replace($delimiter, '', ucwords($string, $delimiter)));
            }

            return $string;
        }
    }
}