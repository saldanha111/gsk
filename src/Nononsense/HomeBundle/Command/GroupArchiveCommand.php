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
class GroupArchiveCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:groupArchive')
		->setDescription('Agrupar registros de archivo para certificación');
	}

	//Misma frecuencia que GroupRetentionCommand

	protected function execute(InputInterface $input, OutputInterface $output){

		$retentions = $this->getPendingRetentions();

		$em2 = $this->getContainer()->get('doctrine')->getManager();
		if ($retentions) {
			
			$url 	= $this->getContainer()->getParameter('api3.url').'/hash';
			$header = ['apiKey:'.$this->getContainer()->getParameter('api3.key')];
			$html="<style>
			        table {
			            font-size: 10px;
			        }
        </style><table style='font-size:11px'><tr><th>Fecha</th><th>Tipo</th><th>ID</th><th>Ácción</th><th>Comentario</th></tr>";
        	
			$group=$this->getNewGroupId();
			foreach ($retentions as $key => $retention) {

				if($retention->getArchivecategory()){
					$type="Categoria";
					$id=$retention->getArchiveCategory()->getId();
				}
				else{
					if($retention->getArchivePreservation()){
						$type="Preservation Notice";
						$id=$retention->getArchivePreservation()->getId();
					}
					else{
						if($retention->getArchiveType()){
							$type="Tipo";
							$id=$retention->getArchiveType()->getId();
						}
						else{
							if($retention->getArchiveState()){
								$type="State";
								$id=$retention->getArchiveState()->getId();
							}
							else{
								if($retention->getArchiveLocation()){
									$type="Location";
									$id=$retention->getArchiveLocation()->getId();
								}
								else{
									if($retention->getArchiveAz()){
										$type="AZ";
										$id=$retention->getArchiveAz()->getId();
									}
									else{
										if($retention->getRecord()){
											$type="Registro";
											$id=$retention->getRecord()->getId();
										}
									}
								}
							}
						}
					}
				}
				$html.="<tr>
					<td>".(($retention->getModified()) ? $this->getContainer()->get('utilities')->sp_date($retention->getModified()->format('d/m/Y H:i:s')) : '')."</td>
                    <td>".$type."</td>
                    <td>".$id."</td>
                    <td>".$retention->getArchiveAction()->getName()."</td>
                    <td>".htmlspecialchars($retention->getDescription(), ENT_QUOTES, 'UTF-8')."</td>
                </tr>";

                $retention->setGroupId($group);
                $em2->persist($retention);
			}

			$html.="</table>";

			try {
				$file = Utils::generatePdf($this->getContainer(), 'GSK - Archivo', 'Listado de registros', $html, 'retention', $this->getContainer()->getParameter('crt.root_dir'));
	            Utils::setCertification($this->getContainer(), $file, 'archivo', $retention->getGroupId());
	            $em2->flush();
			} catch (\Exception $e) {
				$subject = 'Error de agrupación en archivo';
				$message = 'Error durante la agrupación de certificación '.$e->getMessage();
				$this->getContainer()->get('utilities')->sendNotification($this->getContainer()->getParameter('support_email'), false, false, false, $subject, $message);
				$output->writeln(['<error>'.$e->getMessage().'</error>']);
			}
		}
	}

	protected function getPendingRetentions(){

		$em2 = $this->getContainer()->get('doctrine')->getManager();

	    $qb = $em2->createQueryBuilder();
	    $retentions = $qb->select('rts')
			->from('NononsenseHomeBundle:ArchiveSignatures', 'rts')
			->where('rts.groupId is NULL')
			->getQuery()
			->getResult();

		return $retentions;
	}

	protected function getNewGroupId(){

		$em2 = $this->getContainer()->get('doctrine')->getManager();

	    $qb = $em2->createQueryBuilder('rts');
	    $qb->select('MAX(rts.groupId) AS maxGroupId');
	    $qb->from('NononsenseHomeBundle:ArchiveSignatures', 'rts');
	    $max=$qb->getQuery()->getSingleScalarResult();
	    if(!$max){
	    	$return=1;
	    }
	    else{
	    	$return=$qb->getQuery()->getSingleScalarResult()+1;
	    }

    	return $return;
	}
}