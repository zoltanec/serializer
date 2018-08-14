<?php

namespace Zoltanec\Serializer {

    /**
     *
     */
    class Serializer
    {
        /**
         * @var null|ConverterInterface
         */
        private $keyConverter;

        /**
         * @var ConverterInterface|null
         */
        private $valueConverter;

        /**
         * @param ConverterInterface|null $keyConverter
         * @param ConverterInterface|null $valueConverter
         */
        public function __construct($keyConverter = null, $valueConverter = null)
        {
            $this->keyConverter   = $keyConverter;
            $this->valueConverter = $valueConverter;
        }

        /**
         * @param $data
         * @param string $class
         * @return mixed
         * @throws \ReflectionException
         */
        public function denormalize($data, string $class)
        {
            if (!class_exists($class)) {
                throw new \RuntimeException('Class '.$class.' not exists');
            }

            $map   = $this->mapOfSetMethods($class);
            $class = new $class();

            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $setter = $this->getSetter($key);

                    if (empty($value) && !is_int($value) && $value !== '0' && $value !== false) {
                        $value = null;
                    }

                    if (array_key_exists($setter, $map)) {
                        /** @var \ReflectionNamedType|null $type */
                        $type = $map[$setter];

                        switch (true) {
                            case empty($value):
                            case is_null($type):
                            case $type->isBuiltin():
                                $this->setBuiltinAttribute($class, $setter, $value);
                                break;
                            case class_exists($type->getName()):
                                $this->deNormalizeClass($class, $setter, $value, $type);
                                break;
                        }
                    } elseif(property_exists($class, $key)) {
                        $class->{$key} = $value;
                    }
                }
            }

            return $class;
        }

        /**
         * @param object $class
         * @return mixed
         * @throws \ReflectionException
         */
        public function normalize(object $class)
        {
            $map = $this->mapOfGetMethods(get_class($class));

            /**
             * @var string $getter
             * @var \ReflectionNamedType $type
             */
            if($map) {
                foreach ($map as $getter => $type) {
                    $value = '';
                    $key   = $this->getKeyByGetter($getter);

                    switch (true) {
                        case is_null($type):
                            $value = $this->getBuiltinAttribute($class, $getter);
                            break;
                        case $type->getName() === 'array':
                            $value = $this->getArrayAttribute($class, $getter);
                            break;
                        case $type->isBuiltin():
                            $value = $this->getBuiltinAttribute($class, $getter);
                            break;
                        case class_exists($type->getName()):
                            $value = $this->getClassAttribute($class, $getter);
                            break;
                    }

                    $this->runConverter($this->valueConverter, $value, $type);
                    $this->runConverter($this->keyConverter, $key, $type);

                    $schema[$key] = $value;
                }
            } else {
                $reflection = new \ReflectionClass($class);

                foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
                    /**
                     * @var DTO|mixed $value
                     */
                    $value = $class->{$property->getName()};
                    $key  = $property->getName();

                    $this->runConverter($this->valueConverter, $value, null);
                    $this->runConverter($this->keyConverter, $key, null);

                    $schema[$key] = $value;
                }
            }

            return $schema ?? [];
        }

        /**
         * @param object $class
         * @return string
         * @throws \ReflectionException
         */
        public function serialize(object $class)
        {
            $data = $this->normalize($class);

            return json_encode($data);
        }

        /**
         * @param array $data
         * @param string $class
         * @return mixed
         * @throws \ReflectionException
         */
        public function deserialize(array $data, string $class)
        {
            $data = $this->denormalize($data, $class);

            return json_decode($data, 1);
        }

        /**
         * @param string $json
         * @return array
         */
        public function decode(string $json): array
        {
            return json_decode($json, 1);
        }

        /**
         * @param array $data
         * @return string
         */
        public function encode(array $data): string
        {
            return json_encode($data);

        }

        /**
         * @param $converters
         * @param $value
         * @param $type
         * @throws \ReflectionException
         */
        private function runConverter($converters, &$value, $type)
        {
            if (is_null($converters)) {
                return;
            }

            if (!is_array($converters)) {
                $converters = [$converters];
            }

            foreach ($converters as $converter) {
                if (!$converter instanceof ConverterInterface) {
                    continue;
                }

                $value = $converter->convert($value, $type);

                if (is_object($value)) {
                    $value = $this->normalize($value);
                }
            }
        }

        /**
         * @param object $class
         * @param $getter
         * @return mixed
         */
        private function getBuiltinAttribute(object $class, $getter)
        {
            return call_user_func([$class, $getter]);
        }

        /**
         * @param object $class
         * @param $getter
         * @return array
         * @throws \ReflectionException
         */
        private function getArrayAttribute(object $class, $getter)
        {
            $array = call_user_func([$class, $getter]);

            foreach ($array as $key => $data) {
                if (is_object($data)) {
                    if (is_string($key)) {
                        $schema[$key] = $this->normalize($data);
                        continue;
                    }
                    $schema[] = $this->normalize($data);
                }
            }

            return $schema ?? [];
        }

        /**
         * @param object $class
         * @param $getter
         * @return array
         * @throws \ReflectionException
         */
        private function getClassAttribute(object $class, $getter)
        {
            $value = call_user_func([$class, $getter]);

            if (empty($value)) {
                return $value;
            }

            return $this->normalize($value);
        }

        /**
         * @param $class
         * @param $setter
         * @param $value
         */
        private function setBuiltinAttribute($class, $setter, $value): void
        {
            call_user_func_array([$class, $setter], [$value]);
        }

        /**
         * @param $class
         * @param $setter
         * @param $value
         * @param \ReflectionNamedType $type
         * @throws \ReflectionException
         */
        private function setClassAttribute($class, $setter, $value, \ReflectionNamedType $type)
        {
            call_user_func_array([$class, $setter], [$this->denormalize($value, $type->getName())]);
        }

        /**
         * @param $class
         * @param $setter
         * @param $value
         * @param \ReflectionNamedType $type
         * @throws \ReflectionException
         */
        private function deNormalizeClass($class, $setter, $value, \ReflectionNamedType $type): void
        {
            if ($this->isMultiArray($value)) {
                foreach ($value as $val) {
                    $this->setClassAttribute($class, $setter, $val, $type);
                }
            } else {
                $this->setClassAttribute($class, $setter, $value, $type);
            }
        }


        /**
         * @param $class
         * @return array
         * @throws \ReflectionException
         */
        private function mapOfSetMethods($class): array
        {
            $methods = (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if (strpos($method->getName(), 'set') === 0) {
                    // я всегда предполагаю что будет всего 1 параметр
                    $parameters = $method->getParameters();

                    $map[$method->getName()] = $this->getParam($parameters[0]);
                }
            }

            return $map ?? [];
        }

        /**
         * @param $class
         * @return array
         * @throws \ReflectionException
         */
        private function mapOfGetMethods($class): array
        {
            $methods = (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if (strpos($method->getName(), 'get') === 0) {
                    // я всегда предполагаю что будет всего 1 параметр
                    $map[$method->getName()] = $method->getReturnType();
                }
            }

            return $map ?? [];
        }

        /**
         * @param \ReflectionParameter $parameter
         * @return null|string
         */
        private function getParam(\ReflectionParameter $parameter)
        {
            if ($parameter->getType() instanceof \ReflectionNamedType) {
                return $parameter->getType();
            }

            return null;
        }

        /**
         * @param string $key
         * @return string
         */
        private function getSetter(string $key): string
        {
            return 'set'.ucfirst($key);
        }

        /**
         * @param string $getter
         * @return bool|string
         */
        private function getKeyByGetter(string $getter): string
        {
            return lcfirst(substr($getter, 3));
        }

        /**
         * @param $array
         *
         * @return bool
         */
        private function isMultiArray($array): bool
        {
            if (!is_array($array)) {
                return false;
            }

            $isMulti = true;

            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $isMulti = false;
                    break;
                }

                // Список обычно состоит из интовых идентификаторов иначе эт опохоже на поле->значение
                if (!is_int($key)) {
                    $isMulti = false;
                    break;
                }
            }

            return $isMulti;
        }
    }
}