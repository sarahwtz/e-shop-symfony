<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProductController extends AbstractController
{
    #[Route('/admin/view/products', name: 'app_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/add/product', name: 'app_product_add')]
    public function new(Request $request, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCreatedAt(new \DateTimeImmutable());

            $uploadedFile = $form['image']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadProductImage($uploadedFile);
                $product->setImage($newFilename);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product created successfully!');

            return $this->redirectToRoute('app_product');
        }

        return $this->render('admin/product/new.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false, 
        ]);
    }

    #[Route('/admin/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/admin/product/edit/{id}', name: 'app_product_edit')]
    public function edit(EntityManagerInterface $em, Request $request, Product $product, UploaderHelper $uploaderHelper): Response
    {
        $originalThumbnail = $product->getImage();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['image']->getData();

            if ($uploadedFile) {
                if ($originalThumbnail) {
                    $uploaderHelper->deleteProductImage($originalThumbnail);
                }
                $newFilename = $uploaderHelper->uploadProductImage($uploadedFile);
                $product->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Product updated successfully!');

            return $this->redirectToRoute('app_product');
        }

        return $this->render('admin/product/new.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true, 
        ]);
    }

    #[Route('/admin/product/delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, Product $product, UploaderHelper $uploaderHelper): Response
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete'.$product->getId(), $submittedToken)) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_product');
        }

        if ($product->getImage()) {
            $uploaderHelper->deleteProductImage($product->getImage());
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Product deleted successfully!');

        return $this->redirectToRoute('app_product');
    }
}
