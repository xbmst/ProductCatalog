<?php

namespace AppBundle\Services;

use AppBundle\Entity\PasswordRecovery;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Validator\Constraints\Email;

class PasswordRecoveryService
{
    private $container;
    private $em;
    private $encoder;
    private $templating;
    private $formUtils;

    public function __construct(Container $container, EntityManager $em, UserPasswordEncoder $passwordEncoder)
    {
        $this->em = $em;
        $this->container = $container;
        $this->encoder = $passwordEncoder;
        $this->templating = $this->container->get('templating');
        $this->formUtils = $this->container->get('form_utils');
    }

    public function generate()
    {
        return md5(sha1(md5(time())));
    }

    public function createNewRecovery(PasswordRecovery $recovery, User $user)
    {
        $token = $this->generate();
        $recovery->setAccessToken($token);
        $recovery->setUser($user);
        $recovery->setExpires('1h');
        $this->em->persist($recovery);
        $this->em->flush();
        $this->sendEmail($user->getEmail(), $token);
    }

    public function sendEmail($email, $token)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Registration at ProductCatalog')
            ->setFrom('exymax@gmail.com')
            ->setTo($email)
            ->setBody(
                $this->templating->render(
                    'password-recovery-email.html.twig', [
                        'access_token' => $token,
                    ]
                )
            );

        $this->container->get('mailer')->send($message);
    }

    public function validateEmail($email)
    {
        $constraint = new Email();
        $validator = $this->container->get('validator');

        return $validator->validate($email, $constraint);
    }

    public function getUserError(User $user)
    {
        $error = null;
        if (!($user instanceof User)) {
            $error = 'There is no user with such email';
        }

        return $error;
    }

    public function getRecoveryEntity($token)
    {
        $recoveryEntity = null;
        if ($token) {
            $recoveryEntity = $this->em->getRepository('AppBundle:PasswordRecovery')->findOneBy(['accessToken' => $token]);
        }

        return $recoveryEntity;
    }

    public function getRecoveryConfirmationForm(FormBuilder $form, $url)
    {

    }

    public function recover(PasswordRecovery $recovery, $plainPassword)
    {
        $user = $recovery->getUser();
        if (!$plainPassword) {
            return;
        }
        $password = $this->encoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $this->em->persist($user);
        $this->em->remove($recovery);
        $this->em->flush();
    }
}
