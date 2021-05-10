<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Nononsense\UtilsBundle\Classes\Utils;

/**
* 
*/
class CertificateCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:certificate')
		->setDescription('Certificar documentos');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$certifications = $this->getCertifications();

		if ($certifications) {
			try {
				$url 	= $this->getContainer()->getParameter('api3.url').'/hash';
				$header = ['apiKey:'.$this->getContainer()->getParameter('api3.key')];

				foreach ($certifications as $key => $certification) {
					if ($certification->getHash()) {
						$crt = Utils::api3($url, $header, 'POST', ['hash' => $certification->getHash()]);
						$output->writeln([$certification->getHash()]);
						$output->writeln([$crt]);
					}
				}

			} catch (\Exception $e) {
				$output->writeln(['<error>'.$e->getMessage().'</error>']);
			}
		}
	}

	protected function getCertifications(){

		$em = $this->getContainer()->get('doctrine')->getManager();

	    $qb 			= $em->createQueryBuilder();
	    $certifications = $qb->select('c')
	    					->from('NononsenseHomeBundle:Certifications', 'c')
	    					->where('c.txHash is NULL')
	    					->getQuery()
	    					->getResult();

		return $certifications;
	}
}