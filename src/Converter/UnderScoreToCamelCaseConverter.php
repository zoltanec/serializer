<?php

namespace Zoltanec\Serializer\Converter {

    use Zoltanec\Serializer\ConverterInterface;

    /**
     *
     */
    class UnderScoreToCamelCaseConverter implements ConverterInterface
    {
        /**
         * @param $key
         * @param \ReflectionNamedType|null $type
         * @return mixed|string
         */
        public function convert($key, ?\ReflectionNamedType $type = null)
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