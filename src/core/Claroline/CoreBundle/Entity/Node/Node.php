<?php

namespace Claroline\CoreBundle\Entity\Node;

use Doctrine\ORM\Mapping as ORM;

/**
 * Node
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_node")
 */
class Node
{
    public function __construct($name = null, $value = null, $type = null)
    {
        $this->created = new \Datetime();
        $this->update($name, $value, $type);
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Node\Type")
     * @ORM\JoinColumn(name="type_id", nullable=false)
     */
    private $type;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Node
     */
    public function setName($name)
    {
        if ($name !== null) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Node
     */
    public function setValue($value)
    {
        if ($value !== null) {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Node
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Node
     */
    public function setModified($modified = null)
    {
        if ($modified) {
            $this->modified = $modified;
        } else {
            $this->modified = new \Datetime();
        }

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set type
     *
     * @param \Claroline\CoreBundle\Entity\Node\Type $type
     * @return Node
     */
    public function setType(\Claroline\CoreBundle\Entity\Node\Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Claroline\CoreBundle\Entity\Node\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Update node
     *
     * @return \Claroline\CoreBundle\Entity\Node
     */
    public function update($name = null, $value = null, $type = null)
    {
        if ($name) {
            $this->setName($name);
        }

        if ($value) {
            $this->setValue($value);
        }

        if ($type) {
            $this->setType($type);
        }

        $this->setModified();
    }
}
