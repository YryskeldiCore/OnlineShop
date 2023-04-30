<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class CreateProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private KernelInterface $kernel;
    private ProductRepository $productRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        ProductRepository $productRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->productRepository = $productRepository;
    }

    #[Route('/api/create/product', name: 'admin_create_product', methods: 'POST')]
    public function index(Request $request): JsonResponse
    {
        $name = $request->request->get('name');
        /** @var UploadedFile $image */
        $image = $request->files->get('image');
        $price = $request->request->get('price');
        $description = $request->request->get('description');

        $productExists = $this->productRepository->findOneBy(['name' => $name]);

        if ($productExists !== null) {
            return $this->json([
                'id' => $productExists->getId(),
                'message' => 'This product is already exists!'
            ]);
        }

        $imgName = $this->upload($image);

        $product = new Product();
        $product->setName($name);
        $product->setDescription($description);
        $product->setImage($imgName);
        $product->setPrice((float)$price);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->json([
            'id' => $product->getId(),
            'message' => 'success'
        ]);
    }

    public function upload(UploadedFile $uploadedFile):string
    {
        $uploadDir = $this->kernel->getProjectDir() . "/public/uploads/";
        $filename = $this->generateFileName($uploadedFile);

        $uploadedFile->move($uploadDir, $filename);

        return $filename;
    }


    private function generateFileName(UploadedFile $file): string
    {
        $hash = base64_encode(hash("sha256", uniqid()));

        return $hash . "." . $file->getClientOriginalExtension();
    }

    /**
     * @Route("/preview/{hash}", methods={"GET"})
     * @param string $hash
     * @return BinaryFileResponse
     */
    public function preview(string $hash): BinaryFileResponse
    {
        $filePath = $this->kernel->getProjectDir() . "/public/uploads/" . $hash;

        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
