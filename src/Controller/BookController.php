<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{

    /**
     * @Route("/", name="books_list")
     * @param  \App\Repository\BookRepository  $bookRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();

        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods={"GET"}, requirements={"id":"\d+"})
     * @param  \App\Entity\Book  $book
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Book $book): Response
    {
        throw new Exception('Not implemented');
    }

    /**
     * @Route("/new", name="book_new_form", methods={"GET"})
     * @param  string  $errorMessage
     *
     * @param  \App\Repository\AuthorRepository  $authorRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newForm(AuthorRepository $authorRepository, string $errorMessage = ''): Response
    {
        return $this->render('book/edit.html.twig', [
            'book'         => [],
            'allAuthors'   => $authorRepository->findAll(),
            'bookAuthors'  => [],
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * @Route("/new", name="book_new", methods={"POST"})
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @param  \App\Repository\AuthorRepository  $authorRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function new(Request $request, AuthorRepository $authorRepository): Response
    {
        $title               = trim($request->request->get('title'));
        $year_of_publication = new \DateTime(trim($request->request->get('year_of_publication')).'-01-01');
        $isbn                = trim($request->request->get('isbn'));
        $pages_amount        = trim($request->request->get('pages_amount'));

        $entityManager = $this->getDoctrine()->getManager();

        $repository   = $this->getDoctrine()->getRepository(Book::class);
        $existingBook = $repository->findOneBy([
            'title' => $title,
            'isbn'  => $isbn,
        ]);
        if (!is_null($existingBook)) {
            return $this->newForm($authorRepository, 'Такая книга уже существует');
        }
        $existingBook = $repository->findOneBy([
            'title'               => $title,
            'year_of_publication' => $year_of_publication,
        ]);
        if (!is_null($existingBook)) {
            return $this->newForm($authorRepository, 'Такая книга уже существует');
        }

        $book = new Book();

        $book->setTitle($title);
        $book->setYearOfPublication($year_of_publication);
        $book->setIsbn($isbn);
        $book->setPagesAmount($pages_amount);

        foreach ($request->request->get('authors') as $postedAuthorId) {
            $author = $authorRepository->findOneBy(['id' => (int)$postedAuthorId]);
            $book->addAuthor($author);
        }

        $entityManager->persist($book);
        $entityManager->flush();

        $cover = $request->files->get('book-cover-input');
        if ($cover) {
            $targetFolder = "{$_SERVER['DOCUMENT_ROOT']}/assets/images/book-covers/";
            $filesystem   = new Filesystem();

            if ($cover->getMimeType() !== "image/jpg") {
                $filesystem->copy($cover->getPathName(), "$targetFolder/{$book->getId()}.jpg", true);
            } elseif ($cover->getMimeType() !== "image/gif") {
                $imageTmp = imagecreatefromgif($cover->getPathName());
                imagejpeg($imageTmp, "$targetFolder/{$book->getId()}.jpg", 85);
                imagedestroy($imageTmp);
            } elseif ($cover->getMimeType() !== "image/png") {
                $imageTmp = imagecreatefrompng($cover->getPathName());
                imagejpeg($imageTmp, "$targetFolder/{$book->getId()}.jpg", 85);
                imagedestroy($imageTmp);
            } else {
                return $this->newForm($authorRepository, 'Повреждённый файл обложки');
            }
        }

        return $this->redirectToRoute('books_list');
    }

    /**
     * @Route("/{id}/edit", name="book_edit_form", methods={"GET"})
     * @param  \App\Entity\Book  $book
     * @param  string  $errorMessage
     *
     * @param  \App\Repository\AuthorRepository  $authorRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editForm(Book $book, AuthorRepository $authorRepository, string $errorMessage = ''): Response
    {
        $bookAuthorsIds = [];
        foreach ($book->getAuthors() as $author) {
            $bookAuthorsIds[] = $author->getId();
        }

        return $this->render('book/edit.html.twig', [
            'book'           => $book,
            'allAuthors'     => $authorRepository->findAll(),
            'bookAuthorsIds' => $bookAuthorsIds,
            'errorMessage'   => $errorMessage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="book_edit", methods={"POST"})
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \App\Entity\Book  $book
     *
     * @param  \App\Repository\AuthorRepository  $authorRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function edit(Request $request, Book $book, AuthorRepository $authorRepository): Response
    {
        $title               = trim($request->request->get('title'));
        $year_of_publication = new \DateTime(trim($request->request->get('year_of_publication')).'-01-01');
        $isbn                = trim($request->request->get('isbn'));
        $pages_amount        = trim($request->request->get('pages_amount'));

        $entityManager = $this->getDoctrine()->getManager();

        $repository   = $this->getDoctrine()->getRepository(Book::class);
        $existingBook = $repository->findOneBy([
            'title' => $title,
            'isbn'  => $isbn,
        ]);
        if (!is_null($existingBook) && $existingBook->getId() !== $book->getId()) {
            return $this->editForm($book, $authorRepository, 'Такая книга уже существует');
        }
        $existingBook = $repository->findOneBy([
            'title'               => $title,
            'year_of_publication' => $year_of_publication,
        ]);
        if (!is_null($existingBook) && $existingBook->getId() !== $book->getId()) {
            return $this->editForm($book, $authorRepository, 'Такая книга уже существует');
        }

        $book->setTitle($title);
        $book->setYearOfPublication($year_of_publication);
        $book->setIsbn($isbn);
        $book->setPagesAmount($pages_amount);


        $postedAuthors = [];
        foreach ($request->request->get('authors') as $postedAuthorId) {
            $postedAuthors[] = $authorRepository->findOneBy(['id' => (int)$postedAuthorId]);
        }
        $bookAuthors = $book->getAuthors()->toArray();
        if (!is_array($bookAuthors)) {
            $bookAuthors = [];
        }
        foreach ($bookAuthors as $author) {
            if (!in_array($author, $postedAuthors, true)) {
                $book->removeAuthor($author);
            }
        }
        foreach ($postedAuthors as $author) {
            if (!in_array($author, $bookAuthors, true)) {
                $book->addAuthor($author);
            }
        }

        $entityManager->persist($book);
        $entityManager->flush();

        $cover = $request->files->get('book-cover-input');
        if ($cover) {
            $targetFolder = "{$_SERVER['DOCUMENT_ROOT']}/assets/images/book-covers/";
            $filesystem   = new Filesystem();

            if ($cover->getMimeType() !== "image/jpg") {
                $filesystem->copy($cover->getPathName(), "$targetFolder/{$book->getId()}.jpg", true);
            } elseif ($cover->getMimeType() !== "image/gif") {
                $imageTmp = imagecreatefromgif($cover->getPathName());
                imagejpeg($imageTmp, "$targetFolder/{$book->getId()}.jpg", 85);
                imagedestroy($imageTmp);
            } elseif ($cover->getMimeType() !== "image/png") {
                $imageTmp = imagecreatefrompng($cover->getPathName());
                imagejpeg($imageTmp, "$targetFolder/{$book->getId()}.jpg", 85);
                imagedestroy($imageTmp);
            } else {
                return $this->editForm($book, $authorRepository, 'Повреждённый файл обложки');
            }
        }

        return $this->redirectToRoute('books_list');
    }

    /**
     * @Route("/{id}/delete", name="book_delete_form", methods={"GET"})
     * @param  \App\Entity\Book  $book
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteForm(Book $book): Response
    {
        return $this->render('book/delete.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="book_delete", methods={"POST", "DELETE"})
     * @param  \App\Entity\Book  $book
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Book $book): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('books_list');
    }

}
