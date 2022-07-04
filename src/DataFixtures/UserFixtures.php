<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->encoder = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User;
        $user->setUsername('demo');
        $user->setPassword($this->encoder->encodePassword($user,'demo'));
        
        // $product = new Product();
        $manager->persist($user);

        $manager->flush();
    }
}
