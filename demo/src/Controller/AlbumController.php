<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageFormType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    private $em;
    private $imageRepository;

    public function __construct(EntityManagerInterface $em, ImageRepository $imageRepository)
    {
        $this->em = $em;
        $this->imageRepository = $imageRepository;
    }

    #[Route('/album', name: 'album')]
    public function index(): Response
    {
        $user = $this->getUser();
        $photos = $this->imageRepository->findBy([
            'user' => $user->getId()
        ]);

        return $this->render('album/index.html.twig', [
            'photos' => $photos
        ]);
    }

    #[Route('/album/add', name: 'add_photo')]
    public function addPhoto(Request $request): Response
    {
        $user = $this->getUser();
        $image = new Image();
        $form = $this->createForm(ImageFormType::class, $image);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPhoto = $form->getData();

            $imagePath = $form->get('url')->getData();
            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                // move image
                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $error) {
                    return new Response($error->getMessage());
                }

                $newPhoto->setUrl('/uploads/' . $newFileName);
                $newPhoto->setUser($user);
            }

            // save data to db
            $this->em->persist($newPhoto);
            $this->em->flush();
            return $this->redirectToRoute('album');
        }

        return $this->render('album/addPhoto.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/album/view/{id}', name: 'view_photo')]
    public function viewPhoto($id): Response
    {
        $image = $this->imageRepository->find($id);

        return $this->render('album/viewPhoto.html.twig', [
            'image' => $image
        ]);
    }

    #[Route('/album/edit/{id}', name: 'edit_photo')]
    public function editPhoto($id, Request $request): Response
    {
        $image = $this->imageRepository->find($id);

        $form = $this->createForm(ImageFormType::class, $image);
        $form->handleRequest($request);

        $imagePath = $form->get('url')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {

                // upload updated image
                if ($image->getUrl() !== null && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $image->getUrl())) {

                    $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                    // upload new file
                    try {
                        $imagePath->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads',
                            $newFileName
                        );

                    } catch (FileException $error) {
                        return new Response($error->getMessage());
                    }

                    $image->setUrl('/uploads/' . $newFileName);
                    $this->em->flush();
                    return $this->redirectToRoute('album');
                    
                }
            } else {
                $image->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('album');
            }
        }

        return $this->render('album/editPhoto.html.twig', [
            'image' => $image,
            'form' => $form->createView()
        ]);
    }
}
