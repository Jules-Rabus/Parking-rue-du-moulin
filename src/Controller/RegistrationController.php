<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\RegistrationFormType;
use App\Security\ClientAuthenthiticatorAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, ClientAuthenthiticatorAuthenticator $authenticator, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // On creer le formulaire d'inscription
        $user = new Client();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($entityManager->getRepository(Client::class)->FindBy(array("email"=>$user->getEmail()))){

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'erreurUnique' => "Cette email est déjà utilisé"
                ]);
            }

            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // On genere un mail de confirmation
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('reservation@parking-rue-du-moulin.fr', 'Parking-rue-du-moulin'))
                    ->to($user->getEmail())
                    ->subject('Merci de vérifier votre mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $email = new Email();
            $email->from(new Address('gestion@parking-rue-du-moulin.fr','Gestion Parking'))
                ->to(new Address('reservation@parking-rue-du-moulin.fr','Copie Mail'))
                ->bcc(new Address('jules200204@gmail.com','Copie Mail'))
                ->subject('Nouveau client '. $user->getEmail())
                ->text("Nouveau client : " . $user->getEmail() . " , ID : " . $user->getId());
            $mailer->send($email);


            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'erreurUnique' => null
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On verifie la confirmation du mail
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Vôtre mail a bien été enregistré.');

        return $this->redirectToRoute('app_register');
    }
}
