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
    private $doctrine;

    /**
     * @InjectParams({
     *     "doctrine"   = @Inject("doctrine"),
     *     "manager"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct($doctrine, $manager)
    {
        $this->manager = $manager;
        $this->doctrine = $doctrine;
        $this->node = $doctrine->getRepository('ClarolineCoreBundle:Node\Node');
        $this->type = $doctrine->getRepository('ClarolineCoreBundle:Node\Type');
        $this->link = $doctrine->getRepository('ClarolineCoreBundle:Node\Link');
    }

    /**
     * Get nodes entities.
     *
     * Example: print_r($this->node->query('content comment content 23 b next'));
     *
     * @TODO create human language query as "Users who like the color blue" or "Comments belonging to user 1"
     */
    public function query($query, $methode = 'getArrayResult')
    {
        $query = explode(' ', $query);

        switch (count($query)) {
            case 6:
                return $this->get(
                    $query[0], $query[1], $query[2], $query[3], $query[4], $query[5]
                )->getQuery()->$methode();
            case 5:
                return $this->get($query[0], $query[1], $query[2], $query[3], $query[4])->getQuery()->$methode();
            case 4:
                return $this->get($query[0], $query[1], $query[2], $query[3])->getQuery()->$methode();
            case 3:
                return $this->get($query[0], $query[1], $query[2])->getQuery()->$methode();
        }

        return null;
    }

    /**
     * Get nodes entities.
     */
    public function get($a, $link, $b, $mixed = null, $filter = 'b', $position = 'back')
    {
        return $this->getAll($mixed, $filter, $position)
            ->andwhere("link_type.name = '$link'")
            ->andWhere("a_type.name = '$a'")
            ->andWhere("b_type.name = '$b'");
    }

    /**
     * Get nodes entities.
     */
    public function getAll($mixed = null, $filter = 'b', $position = 'back')
    {
        $query = $this->doctrine->getManager()->createQueryBuilder()
            ->select('link', 'a', 'b', 'a_type', 'b_type', 'link_type')
            ->from('ClarolineCoreBundle:Node\Link', 'link')
            ->join('link.a', 'a')
            ->join('link.b', 'b')
            ->join('a.type', 'a_type')
            ->join('b.type', 'b_type')
            ->join('link.type', 'link_type')
            ->Where("link.$position is null");

        if (is_array($mixed)) {

            foreach ($mixed as $field => $value) {
                $query->andWhere("$filter.$field = '$value'");
            }

        } else if (is_numeric($mixed)) {
            $query->andWhere("$filter.id = '$mixed'");
        } else if (is_string($mixed)) {
            $query->andWhere("$filter.value = '$mixed'");
        } else if (is_object($mixed)) {
            $query->andWhere("$filter.id = '".$mixed->getId()."'");
        }

        return $query;
    }

    /**
     * Get entity by id or filter as "array('foo' => 'foo')".
     */
    public function getEntity($entity, $mixed = null)
    {
        if (is_numeric($mixed)) {
            return $entity->find($mixed);
        } else if (is_array($mixed)) {
            $array = $entity->findBy($mixed);

            if (is_array($array) and count($array) == 1) {
                return $array[0];
            }

            return $array;
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
        $a = $this->getNode($a);
        $type = $this->getType($type);
        $b = $this->getNode($b);

        return $this->get(
            $a->getType()->getName(),
            $type->getName(),
            $b->getType()->getName(),
            $b->getId(),
            'b',
            'back'
        )->getQuery()->getOneOrNullResult();
    }

    /**
     * Get last Node
     */
    public function getLast($a, $type, $b)
    {
        $a = $this->getNode($a);
        $type = $this->getType($type);
        $b = $this->getNode($b);

        return $this->get(
            $a->getType()->getName(),
            $type->getName(),
            $b->getType()->getName(),
            $b->getId(),
            'b',
            'next'
        )->getQuery()->getOneOrNullResult();
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

        return $node;
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

        return $type;
    }

    /**
     * Create or update Link entity.
     */
    public function updateLink($a, $type, $b, $size = 12, $id = null)
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
            $size
        );
        $this->manager->persist($link);
        $this->manager->flush();

        return $link;
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
    public function insetFirst($a, $type, $b, $size = 12)
    {
        return $this->updateLink($a, $type, $b, $size);
    }

    /**
     *
     */
    public function insertLast($a, $type, $b, $size = 12)
    {
        $link = $this->updateLink($a, $type, $b, $size);

        $this->reorder($link, $this->getLast($a, $type, $b), $type);

        return $link;
    }

    /**
     *
     */
    public function insertAfter($a, $type, $b, $c, $size = 12)
    {
        $link = $this->updateLink($a, $type, $b, $size);

        $this->reorder($link, $c, $type);

        return $link;
    }

    /**
     *
     */
    public function insertBefore($a, $type, $b, $c, $size = 12)
    {
        $link = $this->updateLink($a, $type, $b, $size);

        $this->reorder($c, $link, $type);

        return $link;
    }

    /*
    public function replaceA($link, $a)
    {
        $link = $this->getLink($link);
        $link->setA($a);

        $this->manager->persist($a);
        $this->manager->flush();
    }

    public function replaceB($link, $b)
    {
        $link = $this->getLink($link);
        $link->setB($b);

        $this->manager->persist($b);
        $this->manager->flush();
    }

    public function isEmpty($a, $type, $b)
    {
        if ($this->getFirst($a, $type, $b)) {
            return true;
        }

        return false;
    }

    public function isFirst($link, $a, $type, $b)
    {
        if ($link == $this->getFirst($a, $type, $b)) {
            return true;
        }

        return false;
    }

    public function isLast($link, $a, $type, $b)
    {
        if ($link == $this->getLast($a, $type, $b)) {
            return true;
        }

        return false;
    }
    */
}
