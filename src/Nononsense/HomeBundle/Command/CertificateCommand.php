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
		$em = $this->getContainer()->get('doctrine')->getManager();

		if ($certifications) {
			try {
				$url 	= $this->getContainer()->getParameter('api3.url').'/hash';
				$header = ['apiKey:'.$this->getContainer()->getParameter('api3.key')];

				foreach ($certifications as $key => $certification) {
					if ($certification->getHash()) {
						$crt = Utils::api3($url, $header, 'POST', ['hash' => $certification->getHash()]);
						$certification->setTxHash(json_decode($crt)->tx_hash);
						$certification->setModified(new \DateTime());
						$em->persist($certification);
						$em->flush();
						$output->writeln([$certification->getHash().'->'.json_decode($crt)->tx_hash]);
					}
				}

			} catch (\Exception $e) {
				$subject = 'Error de certificación';
				$message = 'Error durante la certificación de un documento: '.$e->getMessage();
				$this->getContainer()->get('utilities')->sendNotification('sergio.saldana@nodalblock.com', false, false, false, $subject, $message);
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