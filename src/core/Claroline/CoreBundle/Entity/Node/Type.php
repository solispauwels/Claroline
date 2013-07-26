<?php

namespace Claroline\CoreBundle\Entity\Node;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_nodetype", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 */
class Type
{
    public function __construct($name = null)
    {
        if ($name) {
            $this->setName($name);
        }
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

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
     * @return Type
     */
    public function setName($name)
    {
        $this->name = $name;

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
}
