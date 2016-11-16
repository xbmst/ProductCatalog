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
        $em = $this->getDoctrine()->getManager();
        $recoveryService = $this->get('password_recovery');
        $recovery = new PasswordRecovery();
        $form = $this->createForm(PasswordRecoveryType::class, $recovery);
        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email');
            $user = $em->getRepository('AppBundle:User')->findBy(['email' => $email]);
            $error = $recoveryService->getUserError($user);
            if ($user instanceof User) {
                $recoveryService->createNewRecovery($recovery, $user);

                return $this->redirectToRoute('password_recovery_confirm');
            }
        }

        return $this->render('password-recovery.html.twig', [
            'form' => $form,
            'btn_send_text' => 'Next',
            'error' => $error,
        ]);
    }

    /**
     * @Route("/password-recovery/confirm/{token}", name="password_recovery_confirm")
     */
    public function passwordRecoveryConfirmAction(Request $request, $token)
    {
        $recoveryService = $this->get('password_recovery');
        $recoveryEntity = $recoveryService->getRecoveryEntity($token);
        if ($recoveryEntity instanceof PasswordRecovery) {
            $form = $this->createForm(PasswordRecoveryType::class, $recoveryEntity, [
                    'action' => $this->generateUrl('password_recovery_confirm'),
                    'class' => 'col s12',
                ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $recoveryEntity->getUser();
                $recoveryService->recover($user, $request->get('password'), $recoveryEntity);

                return $this->redirect($this->generateUrl('login'));
            }

            return $this->render('password-recovery-confirm.html.twig', [
                'form' => $form,
                //'error' => $error,
            ]);
        } else {
            return $this->redirect($this->generateUrl('password_recovery', ['error' => 'Invalid recovery address, try again']));
        }
    }
}
