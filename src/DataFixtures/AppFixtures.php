<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        // Super admin

        $client = new Client();

        $client->setName('Bilemo');
        $client->setDescription('La meilleure entreprise de tous les temps!');
        $client->setAddress(' 1 place de la défense, Paris');
        $client->setPhoneNumber('+33836656565');

        $user = new User();

        $user->setEmail('admin@bilemo.com');
        $user->setUserName('admin');
        $user->setPhoneNumber('+33836656565');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->encoder->encodePassword($user, 'admin'));
        $user->setClient($client);

        $manager->persist($user);
        $manager->persist($client);

        // Fake users

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
                $user->setUserName($faker->userName);
                $user->setPhoneNumber($faker->e164PhoneNumber);
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($this->encoder->encodePassword($user, bin2hex(random_bytes(12))));
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
