<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MoviesController extends AbstractController
{
    private EntityManagerInterface $em;
    private MovieRepository $movieRepository;

    public function __construct(MovieRepository $movieRepository, EntityManagerInterface $em)
    {
        $this->movieRepository = $movieRepository;
        $this->em = $em;
    }

    #[Route('/movies', name: 'movies', methods: ['GET'])]
    public function index(): Response
    {
        //find all select * from movies
        //find() select * from movies where id=5;
        //findBy() select * from movies ORDER BY  id DESC;
        //findOneBy() select * from movies where id = 5 and title = 'The dark knight' ORDER BY  id DESC;
        // count() select count() from movies where id = 5

        $movies = $this->movieRepository->findAll();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies
        ]);
    }

    #[Route('/movies/create', name: 'create_movie')]
    public function create(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newMovie = $form->getData();

            $imagePath = $form->get('imagePath')->getData();
            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
                $newMovie->setImagePath('/uploads/' . $newFileName);

//start API call
                $movie_name_form=$form->get('title')->getData();
                $movie_year_form=$form->get('releaseYear')->getData();

                $movie_name=str_replace(" ","-",$movie_name_form);
                $api_key="1d7b9f59";
                $url="https://www.omdbapi.com/?t={$movie_name}&y={$movie_year_form}&apikey={$api_key}";

                $json=$this->file_get_contents_curl($url);
                $data_arr=json_decode($json,TRUE);

//end api call

                $newMovie->setDescription($data_arr['Plot']);
                $newMovie->setImagePath($data_arr['Poster']);
                $newMovie->setTitle($data_arr['Title']);

            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/create.html.twig', [
            'form' => $form->createView()
        ]);

    }
public function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}
    #[Route('/movies/edit/{id}', name: 'edit_movies')]
    public function edit($id, Request $request): Response
    {

        $movie = $this->movieRepository->find($id);
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('imagePath')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {


                if ($movie->getImagePath() !== null) {
                    //dd($movie->getImagePath());
                    // die($this->getParameter('kernel.project_dir') . $movie->getImagePath());
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . "/public" . $movie->getImagePath()
                    )) {

                        // $this->getParameter('kernel.project_dir') . $movie->getImagePath();

                        $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                        try {
                            $imagePath->move(
                                $this->getParameter('kernel.project_dir') . '/public/uploads',
                                $newFileName
                            );
                        } catch (FileException $e) {
                            return new Response($e->getMessage());
                        }

                        $movie->setImagePath('/uploads/' . $newFileName);
                        $this->em->flush();

                        return $this->redirectToRoute('movies');

                    }
                }
            } else {
                $movie->setTitle($form->get('title')->getData());
                $movie->setReleaseYear($form->get('releaseYear')->getData());
                $movie->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('movies');

            }
        }

        return $this->render('movies/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView()
        ]);
    }

    #[Route('/movies/delete/{id}', name: 'delete_movies', methods: ['GET', 'DELETE'])]
    public function delete($id): Response
    {

        $movie = $this->movieRepository->find($id);
        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies');
    }


    #[Route('/movies/{id}', name: 'show_movies', methods: ['GET'])]
    public function show($id): Response
    {

        $movie = $this->movieRepository->find($id);

        return $this->render('movies/show.html.twig', [
            'movie' => $movie
        ]);
    }


}
