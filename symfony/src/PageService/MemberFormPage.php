<?php

namespace App\PageService;

use App\Entity\Member;
use App\Entity\User;
use App\Entity\Email;
use App\Form\MemberType;
use App\Helper\Mattermost;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Sonata\PageBundle\Page\TemplateManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class MemberFormPage implements PageServiceInterface
{
    private $templateManager;
    private $em;
    private $name;
    private $mailer;
    private $formF;
    private $bag;
    private $twig;
    private $mm;

    public function __construct($name, 
        TemplateManager $templateManager, 
        EntityManagerInterface $em, 
        \Swift_Mailer $mailer, 
        FormFactoryInterface $formF,
        ParameterBagInterface $bag,
        Environment $twig,
        Mattermost $mm
    )
    {
        $this->name             = $name;
        $this->templateManager  = $templateManager;
        $this->em               = $em;
        $this->mailer           = $mailer;
        $this->formF            = $formF;
        $this->bag              = $bag;
        $this->twig             = $twig;
        $this->mm               = $mm;
    }
    public function getName(){ return $this->name;}

    public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null)
    {
        $member = new Member();
        $form = $this->formF->create(MemberType::class, $member);
        $form->handleRequest($request);
        $state=null;
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $memberRepo = $this->em->getRepository(Member::class);
            $name = $memberRepo->getByName($member->getFirstname(), $member->getLastname());
            $email = $memberRepo->getByEmail($member->getEmail());
            if(!$name && !$email){
                $user = new User();
                $user->setMember($member);
                $user->setPassword(bin2hex(openssl_random_pseudo_bytes(20)));
                $member->setLocale($request->getlocale());
                $member->setUser($user);
                $this->em->persist($user);
                $this->em->persist($member);
                $this->em->flush();
                $this->sendEmailToMember('member', $member, $this->em, $this->mailer);
                $code = $this->addToInfoMailingList($member);
                $this->announceToMattermost($member);
                $state = 'added';
            } else {
                $state = 'update';
            }
        }

        return $this->templateManager->renderResponse(
            $page->getTemplateCode(), 
            array_merge($parameters,array('form'=>$form->createView(), 'state'=>$state)), 
            $response
        );
    }
    protected function sendEmailToMember($purpose, $member, $em, $mailer)
    {
        $email = $em->getRepository(Email::class)->findOneBy(['purpose' => $purpose]);
        $message = new \Swift_Message($email->getSubject());
        $message->setFrom([$this->bag->get('mailer_sender_address')], "Tunkki");
        $message->setTo($member->getEmail());
        $message->setBody(
            $this->twig->render(
               'emails/base.html.twig',
                   [
                       'email' => $email,
                   ]
               ),
               'text/html'
            );
        $mailer->send($message);
    }
    public function addToInfoMailingList($member)
    {
        $client = HttpClient::create();
        $response = $client->request('POST', 'https://list.ayy.fi/mailman/subscribe/entropy-tiedotus',[
            'query' => [
                'email' => $member->getEmail(),
                'fullname' => $member->getName()
            ]
        ]);
        return $response->getStatusCode();
    }
    protected function announceToMattermost($member)
    {
        $text = '**New Member: '.$member.'**';
        $this->mm->SendToMattermost($text);
    }
    
}
