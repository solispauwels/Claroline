<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
//use JMS\DiExtraBundle\Annotation\InjectParams;
//use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Claroline\CoreBundle\Entity\Node\Node;
//use Claroline\CoreBundle\Entity\Node\Type;
//use Claroline\CoreBundle\Entity\Node\Link;
//use Claroline\CoreBundle\Manager\NodeManager;

class LoadNodeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    private $node;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->node = $this->container->get("claroline.manager.node_manager");

        $types = $this->loadTypes(
            array(
                'adress',
                'contry',
                'city',
                'region',
                'zip',
                'checkbox',
                'color',
                'content',
                'date',
                'datetime',
                'email',
                'file',
                'image',
                'lang',
                'menu',
                'phone',
                'privilege',
                'radio',
                'range',
                'role',
                'settings',
                'text',
                'time',
                'url'
            )
        );

        $links = $this->loadTypes(
            array(
                'comment',
                'create',
                'friend',
                'have',
                'is',
                'like'
            )
        );

        $privileges = $this->loadPrivileges(
            $types,
            array(
                'read',
                'update',
                'delete',
                'reorder'
            )
        );

        //$a = $this->node->updateNode('title1', 'value1', $content);
        //$this->node->updateLink($a, $comment, $b);
    }

    public function loadTypes($types)
    {
        $array = array();

        foreach ($types as $type) {
            $array[$type] = $this->node->updateType($type);
        }

        return $array;
    }

    public function loadPrivileges($types, $privileges)
    {
        $array = array();
        $kinds = array('own', 'all');

        foreach ($privileges as $privilege) {
            foreach ($kinds as $kind) {
                $array[$privilege."_".$kind] = $this->node->updateNode($privilege, $kind, $types['privilege']);
            }
        }

        return $array;
    }
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 9;
    }
}
