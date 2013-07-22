<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Claroline\CoreBundle\Entity\Node\Type;
use Claroline\CoreBundle\Entity\Node\Node;
use Claroline\CoreBundle\Entity\Node\Link;

/**
 * @Service("claroline.manager.node_manager")
 */
class NodeManager
{
    private $type;
    private $node;
    private $link;
    private $manager;

    /**
     * @InjectParams({
     *     "doctrine"        = @Inject("doctrine"),
     *     "manager"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct($doctrine, $manager)
    {
        $this->manager = $manager;
        $this->node = $doctrine->getRepository('ClarolineCoreBundle:Node\Node');
        $this->type = $doctrine->getRepository('ClarolineCoreBundle:Node\Type');
        $this->link = $doctrine->getRepository('ClarolineCoreBundle:Node\Link');
    }

    /**
     * Get nodes entities.
     *
     * @TODO create human language query as "Users who like the color blue" or "Comments belonging to user 1"
     */
    public function get($a, $link, $b, $value, $limit = 30)
    {
        $array = array();
        $first = $this->getFirst(
            $a, $link, $this->getNode(array('type' => $this->getType($b), 'value' => $value))
        );

        for ($i = 0; $i < $limit or $first != null; $i++) {
            $array[] = $first->getNode();
            $first = $first->getNext();
        }

        return $array;
    }

    /**
     * Get entity by id or filter as "array('foo' => 'foo')".
     */
    public function getEntity($entity, $mixed = null)
    {
        if (is_numeric($mixed)) {
            return $entity->find($mixed);
        } else if (is_array($mixed)) {
            return $entity->findOneBy($mixed);
        } else if (is_object($mixed)) {
            return $mixed;
        }

        return null;
    }

    /**
     * Get Nodes by type, Id or filter as "array('name' => 'foo', type => $type)".
     */
    public function getNode($filter = null)
    {
        if (is_string($filter)) {
            return $this->getEntity($this->node, array('type' => $this->getType($filter)));
        }

        return $this->getEntity($this->node, $filter);
    }

    /**
     * Get Types by Type name, Id or filter as "array('name' => 'foo')".
     */
    public function getType($filter = null)
    {
        if (is_string($filter)) {
            return $this->getEntity($this->type, array('name' => $filter));
        }

        return $this->getEntity($this->type, $filter);
    }

    /**
     * Get Links by Id, Value or filter as "array('size' => 12)".
     */
    public function getLink($filter = null)
    {
        return $this->getEntity($this->link, $filter);
    }

    /**
     * Get first Node
     */
    public function getFirst($a, $type, $b)
    {
        return $this->getLink(
            array(
                "a" => $this->getNode($a),
                "b" => $this->getNode($b),
                "type" => $this->getType($type),
                "back" => null
            )
        );
    }

    /**
     * Get last Node
     */
    public function getLast($a, $link, $b)
    {
        return $this->getLink(
            array(
                "a" => $this->getNode($a),
                "b" => $this->getNode($b),
                "type" => $this->getType($link),
                "next" => null
            )
        );
    }

    /**
     * Create or update Node entity.
     */
    public function updateNode($name = null, $value = null, $type = null, $id = null)
    {
        if ($id) {
            $node = $this->getNode($id);
            $node->update($name, $value, $this->getType($type));
        } else {
            $node = new Node($name, $value, $this->getType($type));
        }

        $this->manager->persist($node);
        $this->manager->flush();
    }

    /**
     * Create or update Type entity.
     */
    public function updateType($name, $id = null)
    {
        if ($id) {
            $type = $this->getType($id);
            $type->update($name);
        } else {
            $type = new Type($name);
        }

        $this->manager->persist($type);
        $this->manager->flush();
    }

    /**
     * Create or update Link entity.
     */
    public function updateLink($a, $type, $b, $size = null, $id = null, $next = null, $back = null)
    {
        if ($id) {
            $link = $this->getLink($id);
        } else {
            $link = new Link($this->getFirst($a, $type, $b));
        }

        $link->update(
            $this->getNode($a),
            $this->getType($type),
            $this->getNode($b),
            $size,
            $this->getLink($next),
            $this->getLink($back)
        );
        $this->manager->persist($link);
        $this->manager->flush();
    }

    /**
     * Delete entity.
     */
    public function deleteEntity($entity, $mixed = null)
    {
        if (!is_object($mixed)) {
            $mixed = $this->getEntity($entity, $mixed);
        }

        $this->manager->remove($mixed);
        $this->manager->flush();
    }

    /**
     * Delete Node entity.
     */
    public function deleteNode($mixed)
    {
        $this->deleteEntity($this->node, $mixed);
    }

    /**
     * Delete Type entity.
     */
    public function deleteType($mixed)
    {
        $this->deleteEntity($this->type, $mixed);
    }

    /**
     * Delete Link entity.
     */
    public function deleteLink($mixed)
    {
        $this->deleteEntity($this->link, $mixed);
    }

    /**
     * Reorder Nodes.
     */
    public function reorder($a, $b, $link)
    {
        return true;
    }

    /**
     *
     */
    public function insetFirst($a, $type, $b, $size)
    {
        $this->updateLink($a, $this->getType($link), $b, null, null, $next, $back);
    }

    /**
     *
     */
    public function insertLast($a, $b, $link)
    {
        return true;
    }

    /**
     *
     */
    public function insertAfter($a, $b, $link)
    {
        return true;
    }

    /**
     *
     */
    public function insertBefore($a, $b, $link)
    {
        return true;
    }

    /**
     *
     */
    public function replace($a, $b, $link)
    {
        return true;
    }

    /**
     *
     */
    public function isEmpty($b, $link)
    {
        return false;
    }

    /**
     *
     */
    public function isFirst($a, $b, $link)
    {
        return false;
    }

    /**
     *
     */
    public function isLast($a, $b, $link)
    {
        return false;
    }
}
