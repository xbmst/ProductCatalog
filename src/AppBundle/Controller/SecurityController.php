<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\PasswordRecoveryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\PasswordRecovery;

class SecurityController extends Controller
{
    private $em = null;

    public function __construct()
    {
        $this->em = $this->getDoctrine()->getManager();
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @Route("/password-recovery", name="password_recovery")
     */
    public function passwordRecoveryAction(Request $request)
    {
        $recoveryService = $this->get('password_recovery');
        $recovery = new PasswordRecovery();
        $form = $this->createForm(PasswordRecoveryType::class, $recovery);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $email = $form->get("email");
            if($email) {
                $user = $this->em->getRepository('AppBundle:User')->findBy(['email' => $email]);
                if($user instanceof User) {
                    $token = $recoveryService->generate();
                    $recovery->setAccessToken($token);
                    $recovery->setUser($user);
                    $recovery->setExpires("1h");
                    $this->em->persist($recovery);
                    $this->em->flush();
                    $recoveryService->sendEmail($email, $token);
                }

            }
            else {
                $this->render('password-recovery.html.twig', [
                    'btn_send_text' => 'Next',
                    'error' => ''
                ]);
            }
        }
        return $this->render('password-recovery.html.twig', [
            'btn_send_text' => 'Next',
            //'error' => ''
        ]);
    }

    /**
     * @Route("/password-recovery/confirm/{token}")
     */
    public function passwordRecoveryConfirmAction(Request $request,$token)
    {
        $recoveryService = $this->get('password_recovery');
        $recoveryEntity = $recoveryService->getRecoveryEntity($token, $this->em);
        if($recoveryEntity instanceof PasswordRecovery) {
            $form = $this->createForm(PasswordRecoveryType::class, $recoveryEntity);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $user = $recoveryEntity->getUser();
                $recoveryService->recover($user, $this->get('password'));
                $this->em->persist($user);
                $this->em->remove($recoveryEntity);
                $this->em->flush();
                return $this->redirect('login.html.twig', [
                    'message' => 'Log in with a new password'
                ]);
            }
            return $this->render('password-recovery-confirm.html.twig', [
                'form' => $form,
                //'error' => $error,
            ]);
        }
        else {
            return $this->redirect('password_recovery');
        }
    }
}
