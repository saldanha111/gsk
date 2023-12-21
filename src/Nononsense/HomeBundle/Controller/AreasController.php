<?php
namespace Nononsense\HomeBundle\Controller;

use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\AreasSignatures;
use Nononsense\HomeBundle\Entity\AreasGroups;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\AreaPrefixes;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AreasController extends Controller
{
    public function listAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $filters=Array();
        $filters2=Array();
        $types=array();

        if(!$request->get("export_excel")){
            if($request->get("page")){
                $filters["limit_from"]=$request->get("page")-1;
            }
            else{
                $filters["limit_from"]=0;
            }
            $filters["limit_many"]=15;
        }
        else{
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }


        if($request->get("name")){
            $filters["name"]=$request->get("name");
            $filters2["name"]=$request->get("name");
        }

        $array_item["filters"]=$filters;
        $array_item["items"] = $this->getDoctrine()->getRepository(Areas::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Areas::class)->count($filters2,$types);

        $url=$this->container->get('router')->generate('nononsense_areas');
        $params=$request->query->all();
        unset($params["page"]);
        if(!empty($params)){
            $parameters=TRUE;
        }
        else{
            $parameters=FALSE;
        }
        $array_item["pagination"]=\Nononsense\UtilsBundle\Classes\Utils::paginador($filters["limit_many"],$request,$url,$array_item["count"],"/", $parameters);
        
        return $this->render('NononsenseHomeBundle:Areas:areas.html.twig',$array_item);
    }

    public function editAction(Request $request, string $id)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $array_item=array();

        $serializer = $this->get('serializer');

        if($id!=0){
            $item = $this->getDoctrine()->getRepository(Areas::class)->findOneBy(array("id"=>$id));
            if(!$item){
                return $this->redirect($this->container->get('router')->generate('nononsense_areas'));
            }
            $array_item["item"] = json_decode($serializer->serialize($item, 'json',array('groups' => array('detail_area'))),true);

            $prefixes = $this->getDoctrine()->getRepository(AreaPrefixes::class)->findBy(array("area" => $item));
            $array_item["prefixes"] = json_decode($serializer->serialize($prefixes, 'json',array('groups' => array('list_prefix'))),true);
            if($item->getFll()){
                $array_item["fll"] = $item->getFll()->getId();
            }

            $array_item["signatures"]=$this->getDoctrine()->getRepository('NononsenseHomeBundle:AreasSignatures')->findBy(array("area" => $item),array("id" => "ASC"));
        }

        $array_item["users"] = $this->getDoctrine()->getRepository(Users::class)->findBy(array(),array("name" => "ASC"));

        if(!$request->get("export_excel") && !$request->get("export_pdf")){
            return $this->render('NononsenseHomeBundle:Areas:area.html.twig',$array_item);
        }
        else{
        
            if($request->get("export_excel")){
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                $phpExcelObject->getProperties();
                $phpExcelObject->setActiveSheetIndex(0)
                 ->setCellValue('A1', "Audit trail Areas - ".$user->getUsername()." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));
                $phpExcelObject->setActiveSheetIndex()
                 ->setCellValue('A2', 'Fecha')
                 ->setCellValue('B2', 'Usuario')
                 ->setCellValue('C2', 'Acción')
                 ->setCellValue('D2', 'Justificación');
            }

            if($request->get("export_pdf")){
                $html='<html><body style="font-size:8px;width:100%"><table autosize="1" style="overflow:wrap;width:100%"><tr style="font-size:8px;width:100%">
                        <th style="font-size:8px;width:15%">Fecha</th>
                        <th style="font-size:8px;width:15%">Usuario</th>
                        <th style="font-size:8px;width:20%">Acción</th>
                        <th style="font-size:8px;width:50%">Justificación</th>
                    </tr>';
            }

            $i=3;
            foreach($array_item["signatures"] as $item){

                if($request->get("export_excel")){
                    $phpExcelObject->getActiveSheet()
                    ->setCellValue('A'.$i, ($item->getModified()) ? $this->get('utilities')->sp_date($item->getModified()->format("d/M/Y H:i:s")) : '')
                    ->setCellValue('B'.$i, $item->getUser()->getName())
                    ->setCellValue('C'.$i, $item->getDescription())
                    ->setCellValue('D'.$i, $item->getJustification());

                    $dom = new \DOMDocument;

                    @$dom->loadHTML($item->getChanges());

                    $tableData = [];

                    $rows = $dom->getElementsByTagName('tr');

                    foreach ($rows as $row) {
                      $cells = $row->getElementsByTagName('td');
                      
                      $rowData = [];

                      foreach ($cells as $cell) {
                        $rowData[] = $cell->textContent;
                      }
                      $tableData[] = $rowData;
                    }

                    foreach ($tableData as $rowData) {

                        $i++;
                        $phpExcelObject->getActiveSheet()
                            ->setCellValue('F'.$i, isset($rowData[0]) ? $rowData[0] : '')
                            ->setCellValue('G'.$i, isset($rowData[1]) ? $rowData[1] : '')
                            ->setCellValue('H'.$i, isset($rowData[2]) ? $rowData[2] : '');
                                       
                        
                    }
                }

                if($request->get("export_pdf")){
                    $html.='<tr style="font-size:8px">
                        <td>'.(($item->getModified()) ? $this->get('utilities')->sp_date($item->getModified()->format("d/M/Y H:i:s")) : '').'</td>
                        <td>'.$item->getUser()->getName().'</td>
                        <td>'.$item->getDescription().'</td>
                        <td>'.$item->getJustification().'</td>
                    </tr>';

                    if($item->getChanges()){
                        $html.='<tr style="font-size:8px"><td colspan="2">'.$item->getChanges().'</td></tr>';
                    }
                }

                $i++;
            }

            if($request->get("export_excel")){
                $phpExcelObject->getActiveSheet()->setTitle('Audit trail Áreas');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                  ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                  'audittrail_areas.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);

                return $response; 
            }

            if($request->get("export_pdf")){
                $html.='</table></body></html>';
                $this->get('utilities')->returnPDFResponseFromHTML($html,"Audit trail Áreas");
            }
        }
                

        
    }

    public function updateAction(Request $request, string $id)
    {   
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();

        try {
            $not_update=0;
            if($id!=0){
                $area = $this->getDoctrine()->getRepository(Areas::class)->findOneById($id);
                $changes=$this->getChangesInArea($request,$area); 
                $desc="Se modifica un nuevo área";
            }
            else{
                $area = new Areas();
                $desc="Se crea un nuevo área";
            }

            $area->setName($request->get("name"));
            $area->setCreated(new \DateTime());
            if($request->get("master_template")){
                $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneById($request->get("master_template"));
                $area->setTemplate($template);
            }

            if($request->get("fll")){
                $fll = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id"=>$request->get("fll")));
                $area->setFll($fll);
            }


            if($request->get("is_active")){
                $area->setIsActive(1);
            }
            else{
                $area->setIsActive(0);
            }

            $em->persist($area);

            $prefixes = $this->getDoctrine()->getRepository(AreaPrefixes::class)->findBy(array("area" => $area));
            foreach($prefixes as $prefix){
                $em->remove($prefix);
            }
            if($request->get("prefixes")){
                foreach($request->get("prefixes") as $key => $prefix){
                    $prefixObj = new AreaPrefixes();
                    $prefixObj->setArea($area);
                    $prefixObj->setName($prefix);
                    $em->persist($prefixObj);
                }
            }

            /* Añadimos audittrail a las fichas de aras */
            $user = $this->container->get('security.context')->getToken()->getUser();
            $signature = new AreasSignatures();
            $signature->setArea($area);
            $signature->setUser($user);
            $signature->setDescription($desc);
            $signature->setJustification($request->get("justification"));
            $signature->setCreated(new \DateTime());
            $signature->setModified(new \DateTime());
            if($changes && $changes!=""){
                $signature->setChanges($changes);
            }
            $em->persist($signature);

            $em->flush();

        }catch (\Exception $e) {
            echo $e->getMessage();die();
            $this->get('session')->getFlashBag()->add(
                    'error',
                    "Error desconocido al intentar guardar los datos del area"
                );
            $route = $this->container->get('router')->generate('nononsense_areas_edit', array("id" => $id));
        
            return $this->redirect($route);
        }


        $route = $this->container->get('router')->generate('nononsense_areas');
        
        return $this->redirect($route);
    }

    public function removegroupAction(Request $request, $id, $groupid)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $row = $em->getRepository('NononsenseHomeBundle:AreasGroups')
                  ->findOneBy(array('agroup' => $groupid, 
                                     'area' => $id)
                        );
        $user = $this->container->get('security.context')->getToken()->getUser();
        $signature = new AreasSignatures();
        $signature->setArea($row->getArea());
        $signature->setUser($user);
        $signature->setDescription("Se elimina de este area el grupo ".$row->getAgroup()->getName());
        $signature->setJustification($request->get("justification"));
        $signature->setCreated(new \DateTime());
        $signature->setModified(new \DateTime());
        $em->persist($signature);

        if (empty($row)) {
            $this->get('session')->getFlashBag()->add(
            'errorDeletingUser',
            'No fune posible eliminar el grupo'
            );
        } else {
            $em->remove($row);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
            'deletedGroup',
            'El grupo ha sido eliminado'
            );
        }
 
        return $this->redirect($this->generateUrl('nononsense_areas_edit', array('id' => $id)));
    }

    public function groupsAction(Request $request, $id)
    {
        
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $groups= $this->getDoctrine()
                      ->getRepository('NononsenseHomeBundle:AreasGroups')
                      ->findGroupsByArea(1, 100000, $id, 'q');
        
        return $this->render('NononsenseGroupBundle:Group:index_areas.html.twig', array(
            'groups' => $groups
        ));
    }

    public function addgroupsAction(Request $request, $id)
    {
        
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository('NononsenseGroupBundle:Groups')
                  ->findAllGroupsNotInArea($id);


        return $this->render('NononsenseGroupBundle:Group:searchgroup.html.twig', array(
            'groups' => $groups,
            'areaId' => $id,
        ));
    }

    public function addbulkAction(Request $request)
    {
        $is_valid = $this->get('app.security')->permissionSeccion('areas_gestion');
        if(!$is_valid){
            return $this->redirect($this->generateUrl('nononsense_home_homepage'));
        }

        $meUser = $this->container->get('security.context')->getToken()->getUser();

        $data = $request->query->get('groups');
        $areaId = $request->query->get('id');
        $groupdata = json_decode($data);
        
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('NononsenseHomeBundle:AreasGroups');
        $area = $em->getRepository('NononsenseHomeBundle:Areas')->find($areaId);
        foreach ($groupdata as $id) {
            $new = new AreasGroups();            
            $new->setArea($area);
            $group = $em->getRepository('NononsenseGroupBundle:Groups')->find($id);
            $new->setAgroup($group);
            $em->persist($new);

            $signature = new AreasSignatures();
            $signature->setArea($area);
            $signature->setUser($meUser);
            $signature->setDescription("Se añade a este area el grupo ".$group->getName());
            $signature->setJustification($request->get("justification"));
            $signature->setCreated(new \DateTime());
            $signature->setModified(new \DateTime());
            $em->persist($signature);
        }
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'addedUsers',
            'The new members have been added.'
            );

        return $this->redirect($this->generateUrl('nononsense_areas_edit', array('id' => $areaId)));
    }

    public function listPrefixesJsonAction(Request $request, int $area)
    {
        $em = $this->getDoctrine()->getManager();
        $array=array();

        $items=$em->getRepository('NononsenseHomeBundle:AreaPrefixes')->findBy(array("area"=>$area));
        $serializer = $this->get('serializer');
        $array_items = json_decode($serializer->serialize($items,'json',array('groups' => array('json_prefix'))),true);
        foreach($array_items as $key => $item){
            $array["prefixes"][$key]["id"]=$item["id"];
            $array["prefixes"][$key]["name"]=$item["name"];
        }

        $response = new Response(json_encode($array), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function getChangesInArea($request,$area){
        $changes="";
        $em = $this->getDoctrine()->getManager();
        
        if($request->get("name")!=$area->getName()) {
            $changes.="<tr><td>Nombre</td><td>".$area->getName()."</td><td>".$request->get("name")."</td></tr>";
        }

        if($request->get("master_template")){
            $template = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneById($request->get("master_template"));
            $area->setTemplate($template);
        }

        if((!$area->getTemplate() && $request->get("master_template")) || ($request->get("master_template") && $request->get("master_template")!=$area->getTemplate()->getId())) {
            if($request->get("master_template")){
                $newTemplate = $this->getDoctrine()->getRepository(TMTemplates::class)->findOneBy(array("id"=>$request->get("master_template")));
                $newTemplate=$newTemplate->getName();
            }
            else{
                $newTemplate="";
            }
            if(!$area->getTemplate()){
                $oldTemplate="";
            }
            else{
                $oldTemplate=$area->getTemplate()->getName();
            }
            $changes.="<tr><td>Plantilla maestra</td><td>".$oldTemplate."</td><td>".$newTemplate."</td></tr>";
        }

        if((!$area->getFll() && $request->get("fll")) || ($request->get("fll") && $request->get("fll")!=$area->getFll()->getId())) {
            if($request->get("fll")){
                $newFll = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array("id"=>$request->get("fll")));
                $newFll=$newFll->getName();
            }
            else{
                $newFll="";
            }
            if(!$area->getFll()){
                $oldFll="";
            }
            else{
                $oldFll=$area->getFll()->getName();
            }
            $changes.="<tr><td>FLL</td><td>".$oldFll."</td><td>".$newFll."</td></tr>";
        }

        if(!$request->get("is_active")){
            $active=FALSE;
        }
        else{
           $active=TRUE;
        }
        if($active!=$area->getIsActive()) {
            if($area->getIsActive()){
                $new="No";
                $old="Si";
            }
            else{
                $new="Si";
                $old="No";
            }
            $changes.="<tr><td>Activo</td><td>".$old."</td><td>".$new."</td></tr>";
        }

        if($request->get('prefixes')){
            $prefixes = $request->get('prefixes');
        }
        else{
            $prefixes=array();
        }

        $saved=$em->getRepository('NononsenseHomeBundle:AreaPrefixes')->findBy(array("area" => $area));
        foreach($saved as $save){
            if (!in_array($save->getName(), $prefixes)) {
                $changes.="<tr><td>Prefijo ".$save->getName()."</td><td>Si</td><td>No</td></tr>";
            }
        }

        foreach ($prefixes as $prefix) {
            $exist=$em->getRepository('NononsenseHomeBundle:AreaPrefixes')->findOneBy(array("area" => $area, "name" => $prefix));
            if(!$exist){
                $changes.="<tr><td>Prefijo ".$prefix."</td><td>No</td><td>Si</td></tr>";
            }
        }

        if($changes!=""){
            $changes="\n<table><tr><td>Campo</td><td>Anterior</td><td>Nuevo</td></tr>".$changes."</table>";
        }

        return $changes;
    }
}