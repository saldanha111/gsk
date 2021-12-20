<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
* 
*/
class EfectiveTemplatesCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:efective-templates')
		->setDescription('Notifica de aquellas plantillas que se pueden poner en vigor')
	    ->addOption(
            'msg',
            InputOption::VALUE_NONE
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$templates = $this->getTemplates();

		if ($templates) {
	    	$subject = 'Plantilla pendiente de puesta en vigor';
	        $message = 'La siguiente plantilla ha llegado a su fecha efectiva y está pendiente su puesta en vigor. ';

		    foreach ($templates as $key => $template) {
		    	$users=array();
		    	$baseUrl = trim($this->getContainer()->getParameter('cm_installation'), '/').$this->getContainer()->get('router')->generate('nononsense_tm_config_detail', array("id" => $template->getId()));
		    	$aux_message=$template->getId()." - Nº: ".$template->getNumber()." - Título: ".$template->getNumber()." - Edición: ".$template->getNumEdition();
		    	foreach($template->getTmWorkflows() as $wf){

		    		if($wf->getAction()->getId()==5){
		    			if($wf->getUserEntiy()){
		    				$users[]=$wf->getUserEntiy();
		    			}
		    			else{
		    				foreach($wf->getGroupEntiy()->getUsers() as $gu){
		    					$users[]=$gu->getUser();
		    				}
		    			}
		    			foreach($users as $user){
				            if ($this->getContainer()->get('utilities')->sendNotification($user->getEmail(), $baseUrl, "", "", $subject, $message.$aux_message)) {
				                
				                $output->writeln(['Mensaje enviado: '.$user->getEmail()]);

				                if ($input->getOption('msg')) {
				                	$output->writeln(['Asunto: '.$subject]);	
				                	$output->writeln(['Cuerpo del mensaje: '.$message]);
				                	$output->writeln(['']);	
				                }

				            }else{

				            	$output->writeln(['<error>Error: '.$user['email'].'</error>']);
				            }
				        }
			        }
		        }
		    }

	    }else{
	    	$output->writeln(['<comment>Ningún plantilla pendiente de su puesta en vigor</comment>']);
	    }

	    $output->writeln(['<info>Proceso completado</info>']);	
	}

	protected function getTemplates(){

		$em = $this->getContainer()->get('doctrine')->getManager();
	    $qb 		= $em->createQueryBuilder();
	    $templates = $qb->select('t')
	    				->from('NononsenseHomeBundle:TMTemplates', 't')
	    				->where('t.effectiveDate <= :today')
	    				->setParameter('today', new \DateTime())
	    				->andWhere('t.tmState = 11')
	    				->getQuery()
	    				->getResult();
	    if ($templates) {
		    return $templates;
		}

		return false;
	}
}