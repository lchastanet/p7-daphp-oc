<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $client = new Client();

            $client->setName($faker->company);
            $client->setDescription($faker->catchPhrase);
            $client->setAddress($faker->address);
            $client->setPhoneNumber($faker->e164PhoneNumber);

            $limit = random_int(3, 15);

            for ($j = 0; $j < $limit; $j++) {
                $user = new User();

                $user->setEmail($faker->email);
                $user->setName($faker->userName);
                $user->setPhoneNumber($faker->e164PhoneNumber);

                $jwtManager = $this->container->get('lexik_jwt_authentication.jwt_manager');

                $user->setToken($jwtManager->create($user));
                $user->setClient($client);

                $manager->persist($user);
            }

            $manager->persist($client);
        }

        for ($i = 0; $i < 30; $i++) {
            $product = new Product();

            $product->setName(ucfirst($faker->word));
            $product->setPrice(mt_rand(9999, 99999) / 100);
            $product->setDescription($faker->text(200));
            $product->setSerialNumber($faker->unique()->isbn13);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
