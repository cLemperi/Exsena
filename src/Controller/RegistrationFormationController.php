<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Formations;
use App\Entity\UserInvite;
use App\Entity\Participant;
use App\Form\UserInviteType;
use App\Entity\FormationUser;
use App\Form\ParticipantType;
use Doctrine\ORM\Mapping\Entity;
use App\Form\FormationInviteType;
use App\Repository\UserRepository;
use App\Form\FormationRegistrationType;
use App\Repository\UserInviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class RegistrationFormationController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/registration/formation/{id}', name: 'add_participant')]
    public function inscription(Formations $formation, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        $participants = $user->getParticipants();
        $form = $this->createForm(FormationRegistrationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formation->addUser($user);
            $this->em->persist($formation);
            $this->em->flush();

            $this->addFlash('success', 'Inscription à la formation réussie!');

            return $this->redirectToRoute('formations');
        }

    return $this->render('registration_formation/index.html.twig', [
        'participants' => $participants,
        'formation' => $formation,
        'form' => $form->createView(),
    ]);
}


    private function register(Formations $formation, User $user): void
    {
        // Créer un enregistrement de FormationUser
        $formationUser = new FormationUser();
        $formationUser->setFormation($formation);
        $formationUser->setUser($user);
        $this->em->persist($formationUser);

        // Enregistrer les utilisateurs invités
        foreach ($formation->getUsers() as $userInvite) {
            $userInvite->setFormation($formation);
            $this->em->persist($userInvite);
        }

        $this->em->flush();
    }

    #[Route('/registration/create', name: 'registration_add_invite')]
    public function addUserInvite(Request $request,): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var Entity::User
         */
        $user = $this->getUser();

        $form = $this->createForm(FormationInviteType::class, $user)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', "L'inscrinscription à la formation à bien était validé");
            return $this->redirectToRoute('registration_add_invite');
        }

        return $this->render('registration_formation/create.html.twig', [
            'controller_name' => 'RegistrationFormationController',
            'form' => $form->createView(),
        ]);
    }
}
