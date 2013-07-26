<?php

namespace Claroline\CoreBundle\Controller\Node;

use Claroline\CoreBundle\Manager\NodeManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Node Controller interface.
 */
class TextController
{
    private $node;
    private $type;
    private $security;
    private $privileges;

    /**
     * @InjectParams({
     *     "node"       = @Inject("claroline.manager.node_manager"),
     *     "security"   = @Inject("security.context")
     * })
     */
    public function __construct(NodeManager $node, $security)
    {
        $this->node = $node;
        $this->type = "text";
        $this->security = $security;
        $this->privileges = $this->getPrivileges();
    }

    /**
     * Get Text

     * @Route("/text/{node}", name="claroline_node_text_get")
     *
     * @Template("ClarolineCoreBundle:Node:text.html.twig")
     *
     * @ParamConverter("node", class = "ClarolineCoreBundle:Node\Node", options = {"id" = "node"})
     *
     */
    public function getAction($node)
    {
        print_r($this->getPrivileges());

        if (isset($this->privilege['read_'.$this->type])) {

            return array(
                'node' => $node,
                'user' => $this->node->query('user create text '.$node->getId()),
                'like' => $this->node->query('user like text '.$node->getId())
            );
        }

        return array();
    }

    public function getPrivileges()
    {
        $privileges = array();

        foreach ($this->security->getToken()->getRoles() as $role) {

            $privileges[] = $this->node->get(
                'privilege', 'have', 'role', $role->getRole()
            )->getQuery()->getArrayResult();
        }

        return $privileges;
    }

    /**
     *
     *
    public function creatorAction($a = null, $b = null)
    {
        return ;
    }

    public function updateAction($a = null)
    {
        return ;
    }

    public function deleteAction($a)
    {
        return ;
    }

    public function reorderAction($a, $b)
    {
        return ;
    }*/
}
