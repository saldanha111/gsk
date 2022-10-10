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
class PhysicallyRemoveRetentionsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:physicallyRemoveRetentions')
		->setDescription('Eliminar registros previamente eliminados de forma física');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		//Remove records
		$records = $this->getRemovedRecords();
		$em2 = $this->getContainer()->get('doctrine')->getManager();
		if ($records) {
			foreach($records as $record){
	            $record->remove();
            	$em2->persist($record);	
            }
		}

		//Remove templates
		$templates = $this->getRemovedTemplates();
		$em2 = $this->getContainer()->get('doctrine')->getManager();
		if ($templates) {
            foreach($templates as $template){
            	echo $template->getId();
            	foreach($template->getRetentions() as $retention){
            		$em->remove($retention);
            	}

            	foreach($template->getTmSignatures() as $signature){
            		foreach($signature->getTmTests() as $test){
            			$em->remove($test);
            		}
            		$em->remove($signature);
            	}


            	
	            $em->remove($template);
            }
		}

		$em2->flush();
	}

	protected function getRemovedRecords(){

		$em2 = $this->getContainer()->get('doctrine')->getManager();

	    $qb = $em2->createQueryBuilder();
	    $retentions = $qb->select('cvr')
			->from('NononsenseHomeBundle:CVRecords', 'cvr')
			->where('cvr.retentionRemovedAt IS NOT NULL')
			->getQuery()
			->getResult();

		return $retentions;
	}

	protected function getRemovedTemplates(){

		$em2 = $this->getContainer()->get('doctrine')->getManager();

		$subQueryBuilder = $em2->createQueryBuilder();
		$subQuery = $subQueryBuilder
		    ->select(['DISTINCT(cvr.template)'])
		    ->from('NononsenseHomeBundle:CVRecords', 'cvr')
		    ->leftJoin("cvr.template", "t")
		    ->where('t.retentionRemovedAt IS NOT NULL')
		    ->getQuery()
		    ->getArrayResult()
		;

	    $qb = $em2->createQueryBuilder();
	    $retentions = $qb->select('t')
			->from('NononsenseHomeBundle:TMTemplates', 't')
			->where('t.retentionRemovedAt IS NOT NULL')
			->andWhere($qb->expr()->notIn('t.id', ':subQuery'))
			->setParameter('subQuery', $subQuery)
			->getQuery()
			->getResult();

		return $retentions;
	}
}