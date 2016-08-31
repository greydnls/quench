<?php

namespace Grey\Quench;

use Grey\Quench\Hydrator\HydrationException;
use Grey\Quench\Hydrator\HydratorInterface;
use Grey\Quench\Hydrator\HydrationSubject;

class Manager
{
    /**
     * @var HydratorInterface[]
     */
    private $hydrators = [];

    /**
     * @param HydratorInterface $hydrator
     *
     * @throws HydrationException
     */
    public function registerHydrator(HydratorInterface $hydrator)
    {
        $entityClass = $hydrator->getEntityClass();

        if (!$entityClass instanceof HydrationSubject){
            throw HydrationException::invalidEntity();
        }

        $this->hydrators[$entityClass] = $hydrator;
    }

    /**
     * @param string $entityClass
     * @param array|null $data
     *
     * @throws HydrationException
     *
     * @return HydrationSubject
     */
    public function hydrate($entityClass, array $data = null)
    {
        if ($data == null) {
            return;
        }

        $hydrator = $this->getHydrator($entityClass);

        if (array_key_exists('id', $data)) {
            return $this->hydrateExisting($hydrator, $data);
        }

        return $this->hydrateNew($entityClass, $hydrator, $data);
    }

    private function getHydrator($entity)
    {
        if (array_key_exists($entity, $this->hydrators)){
            return $this->hydrators[$entity];
        }

        throw HydrationException::notFound();
    }

    /**
     * @param HydratorInterface $hydrator
     * @param $data
     *
     * @return HydrationSubject
     *
     * @throws HydrationException
     */
    private function hydrateExisting(HydratorInterface $hydrator, $data)
    {
        if (method_exists($hydrator, 'hydrateExisting')) {
            return $hydrator->hydrateExisting($data);
        }

        $subject = $hydrator->findExisting($data['id']);

        foreach ($data as $fieldName => $value){
            $setterMethod = $this->getSetterMethod($fieldName);

            if (!in_array($hydrator->getRelationshipsToHydrate(), $fieldName) && method_exists($subject, $setterMethod)){
                $subject->$setterMethod($value);
            }
        }

        foreach ($hydrator->getRelationshipsToHydrate() as $relationship){
            if (array_key_exists($relationship, $data)) {
                $setterMethod = $this->getSetterMethod($relationship);

                if (method_exists($subject, $setterMethod)){
                    $hydratedRelationship = $this->hydrateRelationship($hydrator, $relationship, $data);
                    $subject->$setterMethod($hydratedRelationship);
                }
            }
        }

        return $subject;
    }


    /**
     * @param string $entityClass
     * @param HydratorInterface $hydrator
     * @param array $data
     *
     * @return HydrationSubject
     *
     * @throws HydrationException
     */
    private function hydrateNew($entityClass, HydratorInterface $hydrator, array $data)
    {
        foreach ($hydrator->getRelationshipsToHydrate() as $relationship) {
            if (array_key_exists($relationship, $data) && $data[$relationship] !== null) {
                $data[$relationship] = $this->hydrateRelationship($hydrator, $relationship, $data);
            }
        }

        if (method_exists($hydrator, 'hydrateNew')) {
            return $hydrator->hydrateNew($data);
        }

        return call_user_func_array([$entityClass, 'fromArray'], [$data]);
    }


    /**
     * @param HydratorInterface $hydrator
     * @param string $relationship
     * @param array $data
     *
     * @return mixed
     *
     * @throws HydrationException
     */
    private function hydrateRelationship(HydratorInterface $hydrator, $relationship, array $data)
    {
        $methodName = 'hydrate' . $this->camelize($relationship);

        if (!method_exists($hydrator, $methodName)){
            throw HydrationException::invalidRelationshipDefined();
        }

        return $hydrator->$methodName($data);
    }

    private function getSetterMethod($fieldName)
    {
        $fieldName = $this->camelize($fieldName);

        return 'set' . $fieldName;
    }

    /**
     * @param string $fieldName
     *
     * @return mixed|string
     */
    private function camelize($fieldName)
    {
        $first = substr($fieldName, 0, 1);
        $rest = substr($fieldName, 1, strlen($fieldName) - 1);
        $fieldName = strtolower($first) . $rest;

        $fieldName = preg_replace('/^[-_]+/', '', $fieldName);
        $fieldName = preg_replace_callback(
            '/[-_\s]+(.)?/u',
            function ($match) {
                return (isset($match[1])) ? strtoupper($match[1]) : '';
            },
            $fieldName
        );
        $fieldName = preg_replace_callback(
            '/[\d]+(.)?/u',
            function ($match) {
                return strtoupper($match[0]);
            },
            $fieldName
        );
        return $fieldName;
    }
}