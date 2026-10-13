<?php

namespace App\DTOs;

abstract class BaseDTO
{/**
     * @param bool $trim_nulls
     * @return array
     */
    public function all(bool $trim_nulls = false): array
    {
        $data = [];

        try {
            $class = new \ReflectionClass(static::class);

            $properties = $class->getProperties(\ReflectionProperty::IS_PUBLIC);

            foreach ($properties as $reflectionProperty) {

                if ($reflectionProperty->isStatic()) {
                    continue;
                }

                $value = $reflectionProperty->getValue($this);

                if ($trim_nulls === true) {
                    if (!is_null($value)) {
                        $data[$reflectionProperty->getName()] = $value;
                    }
                } else {
                    $data[$reflectionProperty->getName()] = $value;
                }

            }
        } catch (\Exception $exception) {

        }

        return $data;
    }

    public function getFilteredQuery($query): mixed
    {
        return $query;
    }
}
