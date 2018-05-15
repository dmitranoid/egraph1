<?php

namespace App\Infractructure\HydratorInterface;

interface HydratorInterface 
{
    /**
     * Create Domain Object from array data
     *
     * @param DomainClass $target
     * @param array $data
     * @return DomainObject
     */
    public function hydrate($target, array $data);


    /**
     * Extract data from domain model to array
     *
     * @param $object
     * @param array $fields
     * @return array|mixed
     */
    public function extract($object, array $fields);


}