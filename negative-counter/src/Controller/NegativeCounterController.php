<?php

namespace App\Controller;

use App\Entity\NumberArray;
use App\Form\NumberArrayType;
use App\Repository\NumberArrayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NegativeCounterController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        NumberArrayRepository $repository
    ): Response {
        $numberArray = new NumberArray();
        $form = $this->createForm(NumberArrayType::class, $numberArray);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Обчислюємо кількість від'ємних елементів
            $numberArray->calculateNegativeCount();

            // Зберігаємо в базу даних
            $entityManager->persist($numberArray);
            $entityManager->flush();

            $this->addFlash('success', 'Результат збережено в базу даних!');

            return $this->redirectToRoute('app_home');
        }

        // Отримуємо всі збережені результати
        $results = $repository->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('negative_counter/index.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);
    }
}
