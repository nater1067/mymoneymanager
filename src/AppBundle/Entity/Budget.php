<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="budget")
 */
class Budget
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="IncomeStream", mappedBy="budget", cascade={"persist"})
     *
     * @var IncomeStream[]
     */
    private $incomeStreams = [];

    /**
     * @ORM\OneToMany(targetEntity="Expense", mappedBy="budget", cascade={"persist"})
     *
     * @var Expense[]
     */
    private $expenses = [];

    public function __construct() {
        $this->incomeStreams = new ArrayCollection();
        $this->expenses = new ArrayCollection();
    }

    public function setIncomeStreams(array $incomeStreams) {
        $this->incomeStreams = new ArrayCollection($incomeStreams);
    }

    public function setExpenses(array $expenses) {
        $this->expenses = new ArrayCollection($expenses);
    }

    public function addExpense(Expense $expense) {
        $this->expenses->add($expense);
    }

    public function addIncomeStream(IncomeStream $incomeStream) {
        $this->incomeStreams->add($incomeStream);
    }

    /**
     * @return IncomeStream[]
     */
    public function getIncomeStreams() {
        return $this->incomeStreams->toArray();
    }

    /**
     * @return Expense[]
     */
    public function getExpenses() {
        return $this->expenses->toArray();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the combined amount of all income streams
     *
     * @return int - the total amount of income in cents
     */
    public function totalIncomeStreams()
    {
        return array_reduce($this->getIncomeStreams(), function ($totalCents = 0, IncomeStream $incomeStream) {
            return $totalCents + ($incomeStream->getAmount() * $incomeStream->getFrequency());
        });
    }

    /**
     * Return the combined amount of all expenses
     *
     * @return int - the total amount of expenses in cents
     */
    public function totalExpenses()
    {
        return array_reduce($this->getExpenses(), function ($totalCents = 0, Expense $expense) {
            return $totalCents + $expense->getAmount();
        });
    }

    /**
     * Return the combined amount of all income streams
     */
    public function getLeftOver()
    {
        return $this->totalIncomeStreams() - $this->totalExpenses();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}