<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ContactType;
use App\Service\DiscordService;
use App\Service\StatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * Root of application, redirect to browser language if defined
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function rootAction(Request $request)
    {
        $locale = $request->getPreferredLanguage($this->getParameter('app_locales_array'));

        return $this->redirectToRoute('bdd_index', ['_locale' => $locale], 301);
    }

    /**
     * Index of application
     *
     * @param Request $request
     * @param StatService $statService
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @Route("/", name="bdd_index", methods={"GET"})
     */
    public function indexAction(Request $request, StatService $statService)
    {
        $ratingFeed = $this
            ->getDoctrine()
            ->getRepository('App:RiddenCoaster')
            ->findBy([], ['updatedAt' => 'DESC'], 6);

        $image = $this
            ->getDoctrine()
            ->getRepository('App:Image')
            ->findLatestImage();

        $stats = $statService->getIndexStats();

        $reviews = $this
            ->getDoctrine()
            ->getRepository('App:RiddenCoaster')
            ->getLatestReviewsByLocale($request->getLocale());

        $missingImages = [];
        if ($user = $this->getUser() instanceof User) {
            $missingImages = $this
                ->getDoctrine()
                ->getRepository('App:RiddenCoaster')
                ->findCoastersWithNoImage($this->getUser());
        }

        return $this->render(
            'Default/index.html.twig',
            [
                'ratingFeed' => $ratingFeed,
                'image' => $image,
                'stats' => $stats,
                'reviews' => $reviews,
                'missingImages' => $missingImages,
            ]
        );
    }

    /**
     * Contact form
     *
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param DiscordService $discord
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/contact", name="default_contact", methods={"GET", "POST"})
     */
    public function contactAction(
        Request $request,
        \Swift_Mailer $mailer,
        DiscordService $discord,
        TranslatorInterface $translator
    ) {
        /** @var Form $form */
        $form = $this->createForm(ContactType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $message = (new \Swift_Message($translator->trans('contact.email.title')))
                ->setFrom($this->getParameter('app_mail_from'), $this->getParameter('app_mail_from_name'))
                ->setTo($this->getParameter('app_contact_mail_to'))
                ->setReplyTo($data['email'])
                ->setBody(
                    $this->renderView(
                        'Default/contact_mail.txt.twig',
                        [
                            'name' => $data['name'],
                            'message' => $data['message'],
                        ]
                    )
                );
            $mailer->send($message);

            // send notification
            $discord->notify('We just received new message from '.$data['name']."\n\n".$data['message']);

            $this->addFlash(
                'success',
                $translator->trans('contact.flash.success', ['%name%' => $data['name']])
            );

            return $this->redirectToRoute('default_contact');
        }

        return $this->render(
            'Default/contact.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/terms", name="default_terms", methods={"GET"})
     */
    public function termsAction()
    {
        return $this->render(
            'Default/terms.html.twig'
        );
    }
}