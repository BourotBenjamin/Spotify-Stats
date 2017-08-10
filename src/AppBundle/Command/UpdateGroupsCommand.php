<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateGroupsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:update:artists')
            ->setDescription('Artists');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em =  $this->getContainer()->get("doctrine")->getManager();
        $user =$em->getRepository("AppBundle:User")->find(1);
        $this->getContainer()->get('app.services.update_groups_service')->updateArtists($user);
        echo((new \DateTime())->format('h:i:s')." - updateArtists\n");
        $em->flush();
    }
}
