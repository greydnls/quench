<?php

namespace Grey\Quench\Hydrator;

interface HydratorInterface
{
    /**
     * @param $id
     * @return Subject
     */
    public function findExisting($id);

    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * @return array
     */
    public function getRelationshipsToHydrate();
}