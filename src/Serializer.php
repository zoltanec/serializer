<?php

namespace Jaddek\Serializer {

    /**
     *
     */
    class Serializer
    {
        /**
         * @var null|ConverterInterface
         */
        private $converter;

        /**
         * @param ConverterInterface|null $converter
         */
        public function __construct(ConverterInterface $converter = null)
        {
            $this->converter = $converter;
        }

        /**
         * @param $data
         * @param string $class
         * @return string
         * @throws \ReflectionException
         */
        public function denormalize($data, string $class)
        {
            if (!class_exists($class)) {
                throw new \RuntimeException('Class '.$class.' not exists');
            }

            $map   = $this->mapOfSetMethods($class);
            $class = new $class();

            foreach ($data as $key => $value) {
                $setter = $this->getSetter($key);


                if (array_key_exists($setter, $map)) {
                    /** @var \ReflectionNamedType|null $type */
                    $type = $map[$setter];

                    switch (true) {
                        case is_null($type):
                        case $type->isBuiltin():
                            $this->setBuiltinAttribute($class, $setter, $value);
                            break;
                        case class_exists($type->getName()):
                            $this->deNormalizeClass($class, $setter, $value, $type);
                            break;
                    }
                }

            }

            return $class;
        }

        /**
         * @param object $class
         * @return array
         * @throws \ReflectionException
         */
        public function normalize(object $class): array
        {
            $map = $this->mapOfGetMethods(get_class($class));

            /**
             * @var string $getter
             * @var \ReflectionNamedType $type
             */
            foreach ($map as $getter => $type) {

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

                $schema[$this->getKeyByGetter($getter)] = $value ?? null;
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

            return json_decode($data ,1);
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

            foreach ($array as $data) {
                $schema[] = $this->normalize($data);
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
            return $this->normalize(call_user_func([$class, $getter]));
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
            if ($this->isMulti($value)) {
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
            $key = lcfirst(substr($getter, 3));

            if ($this->converter) {
                $key = $this->converter->convert($key);
            }

            return $key;
        }

        /**
         * @param $arr
         * @return bool
         */
        private function isMulti($arr)
        {
            return isset($arr[0]) && is_array($arr[0]);
        }
    }
}