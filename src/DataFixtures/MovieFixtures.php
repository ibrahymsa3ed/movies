<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $movie = new Movie();
        $movie->setTitle('The dark knight');
        $movie->setReleaseYear(2008);
        $movie->setDescription('This is description of dark knight');
        $movie->setImagePath('https://cdn.pixabay.com/photo/2021/06/18/11/24/batman-6345922_1280.jpg');
        //add data to paviot table
        $movie->addActor($this->getReference('actor_1'));
        $movie->addActor($this->getReference('actor_2'));

        $manager->persist($movie);

        $movie2 = new Movie();
        $movie2->setTitle('inception');
        $movie2->setReleaseYear(2010);
        $movie2->setDescription('This is description of inception');
        $movie2->setImagePath('https://cdn.pixabay.com/photo/2016/05/04/12/09/church-1371279_1280.jpg');
        //add data to paviot table
        $movie->addActor($this->getReference('actor_3'));
        $movie->addActor($this->getReference('actor_4'));


        $manager->persist($movie2);

        $manager->flush();
    }
}
