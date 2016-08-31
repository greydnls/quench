<?php

namespace Grey\Quench\Hydrator;


interface HydrationSubject
{
    /**
     * @param array|null $data
     *
     * @return HydrationSubject
     */
    public function fromArray(array $data = null);
}