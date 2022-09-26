<?php

namespace App\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService as BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Validator\ErrorElement;
use Doctrine\ORM\EntityManagerInterface;
use App\Helper\ZMQHelper;

class DoorInfoBlock extends BaseBlockService
{
    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        if (is_null($this->security->getUser())) {
            return $this->renderResponse($blockContext->getTemplate(), [
            ], $response);
        }
        $member = $this->security->getUser()->getMember();
        $now = new \DateTime('now');
        $status = $this->zmq->send('dev'.' init: '.$member->getUsername().' '.$now->getTimestamp());
        $logs = $this->em->getRepository('App:DoorLog')->getLatest(3);
        return $this->renderResponse($blockContext->getTemplate(), [
            'block'     => $blockContext->getBlock(),
            'settings'  => $blockContext->getSettings(),
            'logs'    => $logs,
            'member'    => $member,
            'status'    => $status
        ], $response);
    }
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $this->buildCreateForm($formMapper, $block);
    }
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
    }

    public function __construct($twig, protected Security $security, protected EntityManagerInterface $em, protected ZMQHelper $zmq)
    {
        parent::__construct($twig);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'block/door_info.html.twig',
        ]);
    }
    public function getBlockMetadata($code = null): Metadata
    {
        return new Metadata($this->getName(), ($code ?? $this->getName()), false, 'messages', [
            'class' => 'fa fa-link',
        ]);
    }
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block): void
    {
    }
    public function getName(): string
    {
        return 'Door Info Block';
    }
}
