<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Expense;
use AppBundle\Entity\IncomeStream;
use AppBundle\Entity\Tag;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * We store currency as the base unit (right now, we only have US cents (pennies))
 * Convert this to localized format for the api consumer
 */
function convertCurrencyForPresentation($baseAmount, $currency) {
    if ($currency == BudgetController::CURRENCY_USD) {
        return $baseAmount / 100;
    }

    throw new Exception("Unsupported currency " . $currency);
}

/**
 * Converts from presentation currency (right now, we only have US dollars) to base currency (pennies)
 */
function convertPresentationCurrencyToBaseCurrency($presentationAmount, $currency) {
    if ($currency == BudgetController::CURRENCY_USD) {
        return $presentationAmount * 100;
    }

    throw new Exception("Unsupported currency " . $currency);
}

class BudgetController extends Controller
{
    const CURRENCY_USD = 'USD';

    /**
     * @Route("/budgets/{budgetId}", name="budget_delete")
     * @Method({"DELETE"})
     *
     * @param $budgetId
     *
     * @return Response
     */
    public function deleteBudget($budgetId)
    {
        $em = $this->getDoctrine()->getManager();

        $budget = $em->getReference("\AppBundle\Entity\Budget", $budgetId);
        $em->remove($budget);

        $em->flush();

        return new Response('', Response::HTTP_OK);
    }

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

            $amount = convertPresentationCurrencyToBaseCurrency($request->request->get('amount', '0'), self::CURRENCY_USD);
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

            $amount = convertPresentationCurrencyToBaseCurrency($request->request->get('amount', '0'), self::CURRENCY_USD);
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
     * @Route("/expense/{expenseId}/tags/{tagName}", name="budget_expense_tag_add")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $expenseId
     * @param $tagName - The name of the tag to add
     * @return JsonResponse
     */
    public function addTagToExpense(Request $request, LoggerInterface $logger, $expenseId, $tagName)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $expenses = $em->getRepository("\AppBundle\Entity\Expense");

        /** @var Tag $tag */
        $tag = $em->getRepository("\AppBundle\Entity\Tag")->find($tagName);

        $logger->warning("Expense Id: " . $expenseId);

        /** @var Expense $expense */
        $expense = $em->getReference("\AppBundle\Entity\Expense", $expenseId);

        $expense->addTag($tag);

        $em->flush();

        return new JsonResponse([
            "status" => "success",
        ]);
    }

    /**
     * @Route("/budget/", name="save_budget")
     * @Method({"POST"})
     */
    public function saveBudgetAction(Request $request)
    {
        $content = $request->getContent();
        if (!empty($content)) {
//            $postBody = json_decode($request->getContent(), true);

            $budget = new Budget();

//            $incomeStreams = array_map(function ($incomeStream) use ($budget) {
//                return new IncomeStream(
//                    convertPresentationCurrencyToBaseCurrency($incomeStream["amount"], self::CURRENCY_USD),
//                    $incomeStream["name"],
//                    $incomeStream["frequency"],
//                    $budget
//                );
//            }, isset($postBody["incomeStreams"]) ? $postBody['incomeStreams'] : []);
//
//            $expenses = array_map(function ($expense) use ($budget) {
//                return new Expense(
//                    convertPresentationCurrencyToBaseCurrency($expense["amount"], self::CURRENCY_USD),
//                    $expense["name"],
//                    $budget
//                );
//            }, isset($postBody["expense"]) ? $postBody['expense'] : []);

            $name = $request->request->get('name', '');
            $budget->setName($name);

//            $budget->setIncomeStreams($incomeStreams);
//
//            $budget->setExpenses($expenses);

            $em = $this->getDoctrine()->getManager();
            $em->persist($budget);
            $em->flush();

            return new JsonResponse($this->serializeBudget($budget));
        }

        return new JsonResponse([
            "status" => "error",
            "message" => "No request body",
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
            return [
                "id" => $budget->getId(),
                "name" => $budget->getName(),
            ];
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

        return new JsonResponse($this->serializeBudget($budget));
    }

    private function serializeBudget(Budget $budget) {

        $incomeStreams = array_map(function (IncomeStream $incomeStream) {
            return [
                "id" => $incomeStream->getId(),
                "key" => $incomeStream->getId(),
                "name" => $incomeStream->getName(),
                "frequency" => $incomeStream->getFrequency(),
                "amount" => convertCurrencyForPresentation($incomeStream->getAmount(), self::CURRENCY_USD),
            ];
        }, $budget->getIncomeStreams());

        $expenses = array_map(function (Expense $expense) {
            return [
                "id" => $expense->getId(),
                "key" => $expense->getId(),
                "name" => $expense->getName(),
                "amount" => convertCurrencyForPresentation($expense->getAmount(), self::CURRENCY_USD),
                "tags" => array_map(function (Tag $tag) {
                    return $tag->getName();
                }, $expense->getTags())
            ];
        }, $budget->getExpenses());

        return [
            "id" => $budget->getId(),
            "name" => $budget->getName(),
            "incomeStreams" => $incomeStreams,
            "expenses" => $expenses,
            "totalIncome" => convertCurrencyForPresentation($budget->totalIncomeStreams(), self::CURRENCY_USD),
            "totalExpenses" => convertCurrencyForPresentation($budget->totalExpenses(), self::CURRENCY_USD),
            "totalLeftOver" => convertCurrencyForPresentation($budget->getLeftOver(), self::CURRENCY_USD),
        ];
    }
}
