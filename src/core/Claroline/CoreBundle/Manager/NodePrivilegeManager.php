<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Claroline\CoreBundle\Entity\Node\Type;
use Claroline\CoreBundle\Entity\Node\Node;
use Claroline\CoreBundle\Entity\Node\Link;

/**
 * @Service("claroline.manager.node_privilege_manager")
 */
class NodePrivilegeManager
{
    private $type;
    private $node;
    private $link;
    private $manager;
    private $doctrine;
    private $security;

    /**
     * @InjectParams({
     *     "doctrine"   = @Inject("doctrine"),
     *     "manager"    = @Inject("claroline.persistence.object_manager"),
     *     "security"   = @Inject("security.context")
     * })
     */
    public function __construct($doctrine, $manager, $security)
    {
        $this->manager = $manager;
        $this->doctrine = $doctrine;
        $this->security = $security;
        $this->node = $doctrine->getRepository('ClarolineCoreBundle:Node\Node');
        $this->type = $doctrine->getRepository('ClarolineCoreBundle:Node\Type');
        $this->link = $doctrine->getRepository('ClarolineCoreBundle:Node\Link');
    }

    /**
     * Get nodes entities.
     */
    public function loadPrivileges()
    {
        //$this->security->getToken()->getRoles();
        //@TODO load only rurent roles

        $query = $this->doctrine->getManager()->createQueryBuilder()
            ->select('link', 'a', 'b', 'a_type', 'b_type', 'link_type')
            ->from('ClarolineCoreBundle:Node\Link', 'link')
            ->join('link.a', 'a')
            ->join('link.b', 'b')
            ->join('a.type', 'a_type')
            ->join('b.type', 'b_type')
            ->join('link.type', 'link_type')
            ->Where("a_type.name = 'role'")
            ->andWhere("b_type.name = 'privilege'");

        /*if (is_array($mixed)) {

            foreach ($mixed as $field => $value) {
                $query->andWhere("$filter.$field = '$value'");
            }

        } else if (is_numeric($mixed)) {
            $query->andWhere("$filter.id = '$mixed'");
        } else if (is_string($mixed)) {
            $query->andWhere("$filter.value = '$mixed'");
        } else if (is_object($mixed)) {
            $query->andWhere("$filter.id = '".$mixed->getId()."'");
        }*/

        return $query->getQuery()->getArrayResults();
    }

    /**
     * Check if an action is granted for a node entity.
     */
    public function isGranted($privilege, $node)
    {
        return false;
    }
}
