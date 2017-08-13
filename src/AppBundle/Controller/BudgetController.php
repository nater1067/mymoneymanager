<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Expense;
use AppBundle\Entity\IncomeStream;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends Controller
{
    /**
     * @Route("/incomeStream/{incomeStreamId}", name="budget_income_stream_delete")
     * @Method({"DELETE"})
     *
     * @param $incomeStreamId
     *
     * @return Response
     */
    public function deleteIncomeStream($incomeStreamId)
    {
        $em = $this->getDoctrine()->getManager();

        $incomeStream = $em->getReference("\AppBundle\Entity\IncomeStream", $incomeStreamId);
        $em->remove($incomeStream);

        $em->flush();

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @Route("/expense/{expenseId}", name="budget_expense_delete")
     * @Method({"DELETE"})
     *
     * @param $expenseId
     *
     * @return Response
     */
    public function deleteExpense($expenseId)
    {
        $em = $this->getDoctrine()->getManager();

        $expense = $em->getReference("\AppBundle\Entity\Expense", $expenseId);
        $em->remove($expense);

        $em->flush();

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @Route("/budget/{budgetId}/incomeStream/", name="budget_income_stream_add")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $budgetId
     *
     * @return JsonResponse
     */
    public function addIncomeStream(Request $request, $budgetId)
    {
        $content = $request->getContent();
        if (!empty($content)) {
            $em = $this->getDoctrine()->getManager();

            $budgets = $em->getRepository("\AppBundle\Entity\Budget");

            /** @var Budget $budget */
            $budget = $budgets->find($budgetId);

            $amount = $request->request->get('amount', '0');
            $name = $request->request->get('name', '');
            $frequency = $request->request->get('frequency', '');

            /**
             * Example request body:
             * {name: "groceries", amount: 10000, frequency: 2}
             */
            $budget->addIncomeStream(new IncomeStream($amount, $name, $frequency, $budget));

            $em->flush();

            return new JsonResponse([
                "status" => "success",
                "budget" => [
                    "id" => $budget->getId(),
                ],
            ]);
        }

        return new JsonResponse([
            "status" => "error",
            "message" => "No request body",
        ]);
    }

    /**
     * @Route("/budget/{budgetId}/expense/", name="budget_expense_add")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $budgetId
     *
     * @return JsonResponse
     */
    public function addExpense(Request $request, $budgetId)
    {
        $content = $request->getContent();
        if (!empty($content)) {
            $em = $this->getDoctrine()->getManager();

            $budgets = $em->getRepository("\AppBundle\Entity\Budget");

            /** @var Budget $budget */
            $budget = $budgets->find($budgetId);

            $amount = $request->request->get('amount', '0');
            $name = $request->request->get('name', '');

            /**
             * Example request body:
             * {name: "groceries", amount: 10000}
             */
            $budget->addExpense(new Expense($amount, $name, $budget));

            $em->flush();

            return new JsonResponse([
                "status" => "success",
                "budget" => [
                    "id" => $budget->getId(),
                ],
            ]);
        }

        return new JsonResponse([
            "status" => "error",
            "message" => "No request body",
        ]);
    }

    /**
     * @Route("/budget/", name="save_budget")
     * @Method({"POST"})
     */
    public function saveBudgetAction(Request $request)
    {
        $postBody = json_decode($request->getContent(), true);

        $budget = new Budget();

        $incomeStreams = array_map(function ($incomeStream) use ($budget) {
            return new IncomeStream(
                $incomeStream["amount"],
                $incomeStream["name"],
                $incomeStream["frequency"],
                $budget
            );
        }, $postBody['incomeStreams']);

        $expenses = array_map(function ($expense) use ($budget) {
            return new Expense(
                $expense["amount"],
                $expense["name"],
                $budget
            );
        }, $postBody['expenses']);

        $budget->setIncomeStreams($incomeStreams);

        $budget->setExpenses($expenses);


        $em = $this->getDoctrine()->getManager();
        $em->persist($budget);
        $em->flush();

        return new JsonResponse([
            "status" => "success",
            "budget" => [
                "id" => $budget->getId(),
            ],
        ]);
    }

    /**
     * @Route("/budgets", name="get_budget_ids")
     * @Method({"GET"})
     */
    public function getBudgetIdsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $budgets = $em->getRepository("\AppBundle\Entity\Budget");

        // TODO - make sure this only uses one MariaDB query!!
        $allBudgets = $budgets->findAll();

        $allBudgetIds = array_map(function(Budget $budget) {
            return $budget->getId();
        }, $allBudgets);

        return new JsonResponse([
            "status" => "success",
            "budget_ids" => $allBudgetIds,
        ]);
    }

    /**
     * @Route("/budget/{budgetId}/", name="get_budget")
     * @Method({"GET"})
     */
    public function getBudgetAction($budgetId)
    {
        $em = $this->getDoctrine()->getManager();

        $budgets = $em->getRepository("\AppBundle\Entity\Budget");

        /** @var Budget $budget */
        $budget = $budgets->find($budgetId);

        $incomeStreams = array_map(function (IncomeStream $incomeStream) {
            return [
                "id" => $incomeStream->getId(),
                "key" => $incomeStream->getId(),
                "name" => $incomeStream->getName(),
                "frequency" => $incomeStream->getFrequency(),
                "amount" => $incomeStream->getAmount(),
            ];
        }, $budget->getIncomeStreams());

        $expenses = array_map(function (Expense $expense) {
            return [
                "id" => $expense->getId(),
                "key" => $expense->getId(),
                "name" => $expense->getName(),
                "amount" => $expense->getAmount(),
            ];
        }, $budget->getExpenses());


        return new JsonResponse([
            "incomeStreams" => $incomeStreams,
            "expenses" => $expenses,
        ]);

        return new JsonResponse([
            "incomeStreams" => [
                [
                    "key" => 1,
                    "name" => "Paycheck",
                    "frequency" => 2,
                    "amount" => 2000,
                ],
                [
                    "key" => 2,
                    "name" => "Investment Income",
                    "frequency" => 1,
                    "amount" => 200,
                ],
                [
                    "key" => 3,
                    "name" => "Consulting",
                    "frequency" => 2,
                    "amount" => 400,
                ],
            ],
            "expenses" => [
                [
                    "key" => 1,
                    "name" => "Mortgage",
                    "amount" => -1300,
                ],
                [
                    "key" => 2,
                    "name" => "HOA",
                    "amount" => -400,
                ],
                [
                    "key" => 3,
                    "name" => "Phone",
                    "amount" => -120,
                ],
                [
                    "key" => 4,
                    "name" => "Internet",
                    "amount" => -60,
                ],
            ]
        ]);
    }
}
