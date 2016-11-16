<?php

namespace AppBundle\Services;

use AppBundle\Entity\PasswordRecovery;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PasswordRecoveryService
{
    private $container;
    private $em;
    private $encoder;
    private $templating;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->encoder = $this->container->get('security.password_encoder');
        $this->templating = $this->container->get('templating');
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
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
            ->setFrom('admin@product_catalog.io')
            ->setTo($email)
            ->setBody(
                $this->templating->renderView(
                    'password-recovery-email.html.twig', [
                        'access_token' => $token,
                    ]
                )
            );

        $this->container->get('mailer')->send($message);
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
            $recoveryEntity = $this->em->getRepository('AppBundle:PasswordRecovery')->findBy(['access_token' => $token]);
        }

        return $recoveryEntity;
    }

    public function recover(User $user, $plainPassword, PasswordRecovery $recovery)
    {
        $password = $this->encoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $this->em->persist($user);
        $this->em->remove($recovery);
        $this->em->flush();
    }
}
