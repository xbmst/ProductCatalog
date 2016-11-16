<?php

namespace AppBundle\Controller;

<<<<<<< HEAD
use AppBundle\Entity\User;
use AppBundle\Form\PasswordRecoveryType;
=======
use AppBundle\Form\LoginForm;
>>>>>>> ea22e7c668f4d1d8f998460c961b1444edc08882
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\PasswordRecovery;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render('security/login.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    /**
<<<<<<< HEAD
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
=======
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached');
>>>>>>> ea22e7c668f4d1d8f998460c961b1444edc08882
    }
}
