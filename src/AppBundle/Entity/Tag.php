<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tag")
 */
class Tag
{
    const SAVINGS = "savings";

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * Tag constructor.
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}