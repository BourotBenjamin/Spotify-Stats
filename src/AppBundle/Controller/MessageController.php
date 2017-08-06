<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Genre;
use AppBundle\Entity\Message;
use AppBundle\Entity\PlayedSong;
use AppBundle\Entity\Song;
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageController extends Controller
{

    /**
     * @Route("/messages/{id}", name="messages")
     */
    public function messagesAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $activUser = $this->get('security.token_storage')->getToken()->getUser();
        $user = $em->getRepository('AppBundle:User')->find($id);
        if (!is_object($user))
            throw new HttpException(404, "User not found");
        if ($request->getMethod() == "POST") {
            $message = new Message($activUser, $user, $request->request->get('message'));
            $em->persist($message);
            $em->flush();
        }
        $em->getRepository('AppBundle:Message')->markMessagesAsReas($user->getId(), $activUser->getId());
        $messages = $em->getRepository('AppBundle:Message')->findBy(array('from' => [$user, $activUser], 'to' => [$user, $activUser]), array('createdAt' => 'DESC'));
        return $this->render('AppBundle:Message:conversation.html.twig', array(
            'messages' => $messages,
            'user' => $user
        ));
    }

    /**
     * @Route("/messages", name="all_messages")
     */
    public function allMessagesAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            return $this->redirectToRoute('messages', array('id' => $request->request->get('id') ));
        }
        $em = $this->getDoctrine()->getManager();
        $activUser = $this->get('security.token_storage')->getToken()->getUser();
        $messages = $em->getRepository('AppBundle:Message')->getLastMessagesByUsers($activUser->getId());
        return $this->render('AppBundle:Message:conversations.html.twig', array(
            'messages' => $messages
        ));
    }

}
