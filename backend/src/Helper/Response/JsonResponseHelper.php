<?php

declare(strict_types=1);

namespace App\Helper\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponseHelper
{

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serialiser;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serialiser = $serializer;
    }

    /**
     * @param mixed $entity
     * @param string[] $serializationGroups
     */
    public function createResponse($entity, array $serializationGroups, int $status): Response
    {
        return new Response(
            $this->serialiser->serialize($entity, 'json', ['groups' => $serializationGroups]),
            $status,
            ['content-type' => 'application/json'],
        );
    }
}
