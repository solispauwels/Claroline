<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceCopyEvent extends LogGenericEvent
{
    const action = 'resource_copy';

    /**
     * Constructor.
     * $resource is the final copy
     * while $source is the original object
     */
    public function __construct($resource, $source)
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
                ),
                'source' => array(
                    'resource' => array(
                        'name' => $source->getName(),
                        'path' => $source->getPathForDisplay()
                    ),
                    'workspace' => array(
                        'name' => $source->getWorkspace()->getName()
                    )
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