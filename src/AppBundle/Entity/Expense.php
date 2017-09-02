<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="expense")
 */
class Expense
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The amount in cents ($1.00 == 100 cents)
     *
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Budget", inversedBy="expenses")
     * @ORM\JoinColumn(name="budget_id", referencedColumnName="id")
     *
     * @var Budget
     */
    private $budget;

    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="expense_tags",
     *   joinColumns={@ORM\JoinColumn(name="expense_id", referencedColumnName="id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="tag", referencedColumnName="name")}
     * )
     *
     * @var ArrayCollection|Tag[]
     */
    private $tags;

    public function __construct($amount, $name, $budget) {
        $this->amount = $amount;
        $this->name = $name;
        $this->budget = $budget;
        $this->tags = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Tag[]
     */
    public function getTags() {
        return $this->tags->toArray();
    }

    /**
     * @return string[]
     */
    public function getTagNames() {
        return array_map(function (Tag $tag) {
            return $tag->getName();
        }, $this->getTags());
    }

    public function addTag(Tag $toAdd) {
        $this->tags->add($toAdd);
    }

    public function removeTag(Tag $toRemove) {
        $this->tags->removeElement($toRemove);
    }
}