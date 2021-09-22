<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="posts")
     */
    public function posts(PostRepository $posts): Response
    {
        $user = $this->getUser();

        return $this->render('post/posts.html.twig', [
            'following' => $user->getFollowing()
        ]);
    }

    /**
     * @Route("/post_detail/{id}", name="post_detail")
     */
    public function post_detail(int $id, PostRepository $posts, Request $req, EntityManagerInterface $em): Response
    {
        $post = $posts->findOneBy(['id' => $id]);
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($this->getUser());

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();
        }

        return $this->render('post/post_detail.html.twig', [
            'post' => $post,
            'comments' => $post->getComments(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add_post", name="add_post")
     */
    public function add_post(Request $req, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $post->setUser($this->getUser());
        $post->setCreatedAt(new DateTimeImmutable('now'));

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();
            return new RedirectResponse($this->generateUrl('posts'));
        }
        return $this->render('post/add_post.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
