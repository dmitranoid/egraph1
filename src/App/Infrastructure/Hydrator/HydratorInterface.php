<?php

namespace App\Infractructure\Hydrator;

interface HydratorInterface 
{
    /**
     * Create Domain Object from array data
     *
     * @param EntityClass $target
     * @param array $data
     * @return EntityObject
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