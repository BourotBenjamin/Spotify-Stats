<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSongsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:update:songs')
            ->setDescription('Songs');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em =  $this->getContainer()->get("doctrine")->getManager();
        $user =$em->getRepository("AppBundle:User")->find(1);
        $this->getContainer()->get('app.services.update_songs_service')->updateSongStats($user);
        $this->getContainer()->get('app.services.update_songs_service')->updateSongAlbumAndPopularity($user);
        $this->getContainer()->get('app.services.update_songs_service')->updateArtistsGenres($user);
        $em->flush();
    }
}
