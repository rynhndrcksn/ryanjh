<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploadController extends AbstractController
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    #[Route('/admin/upload-image', name: 'admin_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return $this->json([
                'error' => 'No file uploaded',
            ],
                Response::HTTP_BAD_REQUEST);
        }

        // Validate the file type
        /** @var UploadedFile $uploadedFile */
        $mimeType     = $uploadedFile->getMimeType();
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowedTypes)) {
            return $this->json([
                'error' => 'Invalid file type',
            ],
                Response::HTTP_BAD_REQUEST);
        }

        // Validate file size (max 10MB)
        if ($uploadedFile->getSize() > 10 * 1024 * 1024) {
            return $this->json([
                'error' => 'File too large',
            ],
                Response::HTTP_BAD_REQUEST);
        }

        $originalFilename = pathinfo((string) $uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $unicodeString    = $this->slugger->slug($originalFilename);
        $newFilename      = $unicodeString.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        try {
            $uploadedFile->move(
                $this->getParameter('kernel.project_dir').'/public/uploads/images',
                $newFilename
            );
        } catch (FileException) {
            return $this->json([
                'error' => 'Upload failed',
            ],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'url' => '/uploads/images/'.$newFilename,
        ],
            Response::HTTP_ACCEPTED);
    }
}
