<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/profile/{id}", name="profile")
     */
    public function profile(int $id, UserRepository $users): Response
    {
        $following = false;

        $user = $users->findOneBy(['id' => $id]);

        $expr = new Comparison('id', '=', `$id`);
        $criteria = new Criteria();
        $criteria->where($expr);

        $follow = $this->getUser()->getFollowing();

        if ($follow->matching($criteria)) {
            $following = true;
        }
        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'following' => $following
        ]);
    }

    /**
     * @Route("/follow/{id}", name="follow")
     */
    public function follow(int $id, EntityManagerInterface $em, UserRepository $users): Response
    {
        $user = $this->getUser();
        $userFollowed = $users->findOneBy(['id' => $id]);
        $user->addFollowing($userFollowed);
        $userFollowed->addFollower($user);
    
        $em->persist($user);
        $em->persist($userFollowed);
        $em->flush();
        return new RedirectResponse($this->generateUrl('posts'));
    }

    /**
     * @Route("/profile/{id}/followers", name="followers")
     */
    public function profileFollowers(int $id, UserRepository $users): Response
    {
        $user = $users->findOneBy(['id' => $id]);

        return $this->render('user/followers.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profile/{id}/following", name="following")
     */
    public function profileFollowing(int $id, UserRepository $users): Response
    {
        $user = $users->findOneBy(['id' => $id]);

        return $this->render('user/following.html.twig', [
            'user' => $user,
        ]);
    }

}
