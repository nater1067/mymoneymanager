<?php
namespace AppBundle\Entity;

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
     * @var Budget
     */
    private $budget;

    public function __construct($amount, $name, $budget) {
        $this->amount = $amount;
        $this->name = $name;
        $this->budget = $budget;
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
}