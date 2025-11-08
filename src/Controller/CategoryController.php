<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Form\CategoryType;

final class CategoryController extends AbstractController
{
    #[Route('admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('admin/add/category', name: 'app_category_add')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category record is created!');

            return $this->redirectToRoute('app_category');
        }

        return $this->render('admin/category/new.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false
        ]);
    }

    #[Route('admin/category/{id}', name: 'app_category_show')]
    public function show(Category $category):Response
    {
            return  $this->render('admin/category/show.html.twig', [
                'category' => $category,
            ]);

    }

     #[Route('admin/category/edit/{id}', name: 'app_category_edit')]
    public function edit(EntityManagerInterface $em, Request $request, Category $category):Response
    {
        $form = $this->createForm(CategoryType::class, $category);   
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->flush();

            $this->addFlash('success', 'Category updated successfully!');

            return $this->redirectToRoute('app_category');
        }

        return  $this->render('admin/category/new.html.twig', [
                'form' => $form->createView(),
                'isEdit' => true,
            ]);

    }

}
