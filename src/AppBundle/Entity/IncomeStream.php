<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="income_stream")
 */
class IncomeStream
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * The amount in cents ($1.00 == 100 cents)
     *
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    private $name;

    /**
     * The frequency in months that we receive this income stream
     *
     * @var @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $frequency;

    /**
     * @ORM\ManyToOne(targetEntity="Budget", inversedBy="incomeStreams")
     * @ORM\JoinColumn(name="budget_id", referencedColumnName="id")
     * @var Budget
     */
    private $budget;

    public function __construct($amount, $name, $frequency, Budget $budget) {
        $this->amount = $amount;
        $this->name = $name;
        $this->frequency = $frequency;
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

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }
}