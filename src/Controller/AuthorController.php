<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/authors")
 */
class AuthorController extends AbstractController
{

    /**
     * @Route("/", name="authors_list")
     * @param  \App\Repository\AuthorRepository  $authorRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * @Route("/{id}", name="author_show", methods={"GET"}, requirements={"id":"\d+"})
     * @param  \App\Entity\Author  $author
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Author $author): Response
    {
        throw new Exception('Not implemented');
    }

    /**
     * @Route("/new", name="author_new_form", methods={"GET"})
     * @param  string  $errorMessage
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newForm(string $errorMessage = ''): Response
    {
        return $this->render('author/edit.html.twig', [
            'author'       => [],
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * @Route("/new", name="author_new", methods={"POST"})
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request): Response
    {
        $surname    = trim($request->request->get('surname'));
        $name       = trim($request->request->get('name'));
        $patronymic = trim($request->request->get('patronymic'));

        $entityManager = $this->getDoctrine()->getManager();

        $repository     = $this->getDoctrine()->getRepository(Author::class);
        $existingAuthor = $repository->findOneBy([
            'surname'    => $surname,
            'name'       => $name,
            'patronymic' => $patronymic,
        ]);
        if (!is_null($existingAuthor)) {
            return $this->newForm('Автор с такими данными уже существует');
        }

        $author = new Author();

        $author->setSurname($surname);
        $author->setName($name);
        $author->setPatronymic($patronymic);

        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('authors_list');
    }

    /**
     * @Route("/{id}/edit", name="author_edit_form", methods={"GET"})
     * @param  \App\Entity\Author  $author
     * @param  string  $errorMessage
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editForm(Author $author, string $errorMessage = ''): Response
    {
        return $this->render('author/edit.html.twig', [
            'author'       => $author,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="author_edit", methods={"POST"})
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \App\Entity\Author  $author
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Author $author): Response
    {
        $surname    = trim($request->request->get('surname'));
        $name       = trim($request->request->get('name'));
        $patronymic = trim($request->request->get('patronymic'));

        $entityManager = $this->getDoctrine()->getManager();

        $repository     = $this->getDoctrine()->getRepository(Author::class);
        $existingAuthor = $repository->findOneBy([
            'surname'    => $surname,
            'name'       => $name,
            'patronymic' => $patronymic,
        ]);
        if (!is_null($existingAuthor)) {
            return $this->editForm($author, 'Автор с такими данными уже существует');
        }

        $author->setSurname($surname);
        $author->setName($name);
        $author->setPatronymic($patronymic);

        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('authors_list');
    }

    /**
     * @Route("/{id}/delete", name="author_delete_form", methods={"GET"})
     * @param  \App\Entity\Author  $author
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteForm(Author $author): Response
    {
        return $this->render('author/delete.html.twig', [
            'author' => $author,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="author_delete", methods={"POST", "DELETE"})
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \App\Entity\Author  $author
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Author $author): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('authors_list');
    }

}
