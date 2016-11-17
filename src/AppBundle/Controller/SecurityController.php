<?php

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Form\PasswordRecoveryType;
use AppBundle\Form\LoginForm;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\PasswordRecovery;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $message = $request->get('message');

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render('security/login.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
            'message' => $message
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
        $form = $recoveryService->getRecoveryForm($this->createFormBuilder());
        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted()) {
            $email = $form->getData()['email'];
            $user = $em->getRepository('AppBundle:User')->findOneBy(['email' => $email]);
            if ($user instanceof User) {
                $recoveryService->createNewRecovery($recovery, $user);

                return $this->redirectToRoute('login', ['message' => 'Check your email for new messages']);
            }
            else {
                $error = 'There is no user with such email';
            }
        }

        return $this->render('password-recovery.html.twig', [
            'form' => $form->createView(),
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
        $error = null;
        if ($recoveryEntity instanceof PasswordRecovery) {
            $url = $this->generateUrl('password_recovery_confirm', ['token' => $token]);
            $form = $recoveryService->getRecoveryConfirmationForm($this->createFormBuilder(), $url);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $recoveryService->recover($recoveryEntity, $form->getData()['password']);

                return $this->redirect($this->generateUrl('login', ['message', 'Password recovered successfully']));
            }

            return $this->render('password-recovery-confirm.html.twig', [
                'form' => $form->createView(),
                'btn_confirm_text' => 'Confirm',
                'error' => $error,
            ]);
        } else {
            return $this->redirect($this->generateUrl('password_recovery', ['error' => 'Invalid recovery address, try again']));
        }
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached');
    }
}
