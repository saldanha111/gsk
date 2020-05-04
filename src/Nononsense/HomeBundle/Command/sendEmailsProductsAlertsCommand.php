<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class sendEmailsProductsAlertsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
		->setName('gsk:sendEmailsProductsAlerts')
		->setDescription('Send email alerts products')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		//EL CRON DEBE CORRER UNA VEZ ADL DIA

		$container = $this->getApplication()->getKernel()->getContainer();

		$em = $container->get('doctrine')->getManager();

		$array_emails_users_gestion_stock = array();
        $connection = $em->getConnection();
        $query = "
            SELECT u.email  
            FROM groupusers AS g
            INNER JOIN users AS u ON u.id=g.user_id
            WHERE group_id=16 ";
        $statement = $connection->prepare($query);
        $statement->execute();
        $usersGroupGestionStock = $statement->fetchAll();
        if($usersGroupGestionStock){
        	foreach ($usersGroupGestionStock as $user) {
        		array_push($array_emails_users_gestion_stock, $user['email']);
        	}
        }
       

        //si el grupo gestion de stock tiene usuarios
        if(count($array_emails_users_gestion_stock)>0){

        	$cm_installation = $container->getParameter('cm_installation');
        	$cm_installation = substr($cm_installation, 0, strlen($cm_installation)-1);

        	//alerta de los productos cuyo stock esta por debajo del stock minimo
        	$query = "SELECT p
					FROM NononsenseHomeBundle:Products p
					WHERE p.stock < p.stockMinimum
					ORDER BY p.name ASC ";
	
			$query = $em->createQuery($query);
			$productsStockUnderMinimum = $query->getResult();

			if(count($productsStockUnderMinimum)>0){
				try{
		            $message = \Swift_Message::newInstance()
			                ->setSubject('Alerta stock productos por debajo del stock mínimo')
			                ->setFrom($container->getParameter('mailer_user'))
			                ->setTo($array_emails_users_gestion_stock)
			                ->setBody(
				    				$container->get('templating')->render(
				    						'NononsenseHomeBundle:Email:notificationProductsUnderStock.html.twig',
				    						array('cm_installation' => $cm_installation,'productsStockUnderMinimum' => $productsStockUnderMinimum)));
			        $message->setContentType("text/html");
			        $container->get('mailer')->send($message);
		        }
		        catch (\Exception $e) {
					$container->get('logger')->critical("Erro en envio alertas productos command");            
		        }	
			}

			//alerta de los productos cuando se alcance su fecha de caducidad
			$date_today = new \DateTime();
        	$query = "SELECT pi
					FROM NononsenseHomeBundle:ProductsInputs pi
					JOIN pi.product p
					WHERE pi.expiryDate <= '".$date_today->format('Y-m-d')."'
					ORDER BY p.name ASC ";
	
			$query = $em->createQuery($query);
			$productsExpired = $query->getResult();

			if(count($productsExpired)>0){
				try{
		            $message = \Swift_Message::newInstance()
			                ->setSubject('Alerta productos fecha caducidad alcanzada')
			                ->setFrom($container->getParameter('mailer_user'))
			                ->setTo($array_emails_users_gestion_stock)
			                ->setBody(
				    				$container->get('templating')->render(
				    						'NononsenseHomeBundle:Email:notificationProductsDateExpired.html.twig',
				    						array('cm_installation' => $cm_installation,'productsExpired' => $productsExpired, 'today' => $date_today->format('Y-m-d'))));
			        $message->setContentType("text/html");
			        $container->get('mailer')->send($message);
		        }
		        catch (\Exception $e) {
					$container->get('logger')->critical("Erro en envio alertas productos command");            
		        }	
			}


			//alerta de los productos cuando se alcance su fecha de destrucción
			$date_today = new \DateTime();
        	$query = "SELECT pi
					FROM NononsenseHomeBundle:ProductsInputs pi
					JOIN pi.product p
					WHERE pi.destructionDate <= '".$date_today->format('Y-m-d')."'
					ORDER BY p.name ASC ";
	
			$query = $em->createQuery($query);
			$productsDestructionDate = $query->getResult();

			if(count($productsExpired)>0){
				try{
		            $message = \Swift_Message::newInstance()
			                ->setSubject('Alerta productos fecha destrucción alcanzada')
			                ->setFrom($container->getParameter('mailer_user'))
			                ->setTo($array_emails_users_gestion_stock)
			                ->setBody(
				    				$container->get('templating')->render(
				    						'NononsenseHomeBundle:Email:notificationProductsDateDestruction.html.twig',
				    						array('cm_installation' => $cm_installation,'productsDestructionDate' => $productsDestructionDate, 'today' => $date_today->format('Y-m-d'))));
			        $message->setContentType("text/html");
			        $container->get('mailer')->send($message);
		        }
		        catch (\Exception $e) {
					$container->get('logger')->critical("Erro en envio alertas productos command");            
		        }	
			}

			
        }

		

		

		$output->writeln("fin");
	}
}