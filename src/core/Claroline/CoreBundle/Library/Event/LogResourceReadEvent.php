<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceReadEvent extends LogGenericEvent
{
    const action = 'resource_read';

    /**
     * Constructor.
     */
    public function __construct($resource)
    {
        parent::__construct(
            self::action,
            array(
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay()
                ),
                'workspace' => array(
                    'name' => $resource->getWorkspace()->getName()
                ),
                'owner' => array(
                    'last_name' => $resource->getCreator()->getLastName(),
                    'first_name' => $resource->getCreator()->getFirstName()
                )
            ),
            null,
            null,
            $resource,
            null,
            $resource->getWorkspace(),
            $resource->getCreator()
        );
    }
}