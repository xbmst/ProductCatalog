<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;

/**
 * @Route("/admin")
 */
class UserAdminController extends Controller
{
    /**
     * @Route("/user", name="admin_user_list")
     */
    public function indexAction()
    {
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();

        return $this->render('user/userList.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * @Route("/user/{id}/edit", name="admin_user_edit")
     */
    public function editAction(Request $request, User $user)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User updated.');

            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('user/userEdit.html.twig', [
            'userForm' => $form->createView(),
            ]

        );
    }
}
