<?php

namespace Grey\Quench\Hydrator;


interface Subject
{
    /**
     * @param array|null $data
     *
     * @return Subject
     */
    public function fromArray(array $data = null);
}