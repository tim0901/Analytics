<?php

declare(strict_types=1);

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use http\Env\Response;
use mysqli;
use phpDocumentor\Reflection\Types\This;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Plates\PlatesRenderer;
use Zend\Expressive\Router;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\ZendView\ZendViewRenderer;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DisplayTablePageHandler implements RequestHandlerInterface
{
    /** @var string */
    private $containerName;

    /** @var Router\RouterInterface */
    private $router;

    /** @var null|TemplateRendererInterface */
    private $template;

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        ?TemplateRendererInterface $template = null
    ) {
        $this->containerName = $containerName;
        $this->router        = $router;
        $this->template      = $template;

    }

    public function getAction(ServerRequestInterface $request, mysqli $connection){

        $desiredTable = $request->getAttribute('desiredTable', 'event_table');
        $desiredColumn = $request->getAttribute('desiredColumn','Event_ID');
        $desiredValue = $request->getAttribute('desiredValue');

        //Check connection was successful
        if($connection->connect_error){
            $data['error'] = ("MySQL connection failed. " . $connection->connect_error);
        }
        else{
            //Main table
            if($desiredTable == "event_table"){
                //Filter table for desired results, or show all
                if($desiredValue !== null){
                    if($desiredColumn !== "Event_ID"){
                        $desiredValue = "%" . $desiredValue . "%";
                    }
                    $sql = "SELECT et.Event_ID, et.Date, mt.Module_Name, et.User, at.Accessed_Name, et.Type, et.Action 
                            FROM event_table AS et 
                            JOIN accessed_table AS at ON et.Accessed = at.Accessed_ID 
                            JOIN modules_table AS mt on at.Module_ID = mt.Module_ID
                            JOIN users_table ut on et.User = ut.User_ID 
                            WHERE ".$desiredColumn." LIKE '".$desiredValue."'";

                    //$sql = "SELECT id, firstname, lastname, email FROM my_table WHERE ".$desiredColumn." LIKE'".$desiredValue."'";
                }
                else{
                    $sql = "SELECT et.Event_ID, et.Date, mt.Module_Name, ut.User_Name, at.Accessed_Name, et.Type, et.Action 
                            FROM event_table AS et 
                            JOIN accessed_table AS at ON et.Accessed = at.Accessed_ID 
                            JOIN modules_table AS mt on at.Module_ID = mt.Module_ID 
                            JOIN users_table ut on et.User = ut.User_ID";
                }

                $result = $connection->query($sql);

                //Check there is data present
                if($result->num_rows > 0){
                    //Table container to be passed to template
                    $t = null;
                    $i = 0;
                    //For each row in the table
                    while($row = $result->fetch_assoc()){

                        $t[$i]['Event_ID'] = $row['Event_ID'];
                        $t[$i]['Date'] = $row['Date'];
                        $t[$i]['Module'] = $row['Module_Name'];
                        $t[$i]['User'] = $row['User_Name'];
                        $t[$i]['Accessed'] = $row['Accessed_Name'];
                        $t[$i]['Type'] = $row['Type'];
                        $t[$i]['Action'] = $row['Action'];
                        $i++;
                    }

                    $data = $t;
                    $connection->close();
                    return new JsonResponse($data);
                }
                else{
                    $connection->close();
                    $data = null;
                    return new JsonResponse($data);
                }
            }
            else{
                if($desiredValue !== null){
                    $sql = "SELECT * FROM " . $desiredTable . " WHERE ".$desiredColumn." LIKE '".$desiredValue."'";
                }
                else{
                    $sql = "SELECT * FROM " . $desiredTable;
                }

                $result = $connection->query($sql);

                if($result->num_rows > 0){
                    //Table container to be passed to template
                    $t = null;
                    $i = 0;
                    //For each row in the table
                    while($row = $result->fetch_assoc()){
                        if($desiredTable == "modules_table"){
                            //Return list of modules
                            $t[$i]['Module_ID'] = $row['Module_ID'];
                            $t[$i]['Module_Name'] = $row['Module_Name'];
                            $i++;
                        }
                        else if($desiredTable == "modules_table"){
                            //Return list of users (hashed)
                            $t[$i]['User_ID'] = $row['User_ID'];
                            $t[$i]['User_Name'] = $row['User_Name'];
                            $i++;
                        }
                        else if($desiredTable == "accessed_table"){
                            $t[$i]['Accessed_ID'] = $row['Accessed_ID'];
                            $t[$i]['Accessed_Name'] = $row['Accessed_Name'];
                        }
                        else{
                            //404 table not found
                            $connection->close();
                            $data = null;
                            return new JsonResponse($data);
                        }
                    }

                    $data = $t;
                    $connection->close();
                    return new JsonResponse($data);
                }
                else{
                    $connection->close();
                    $data = null;
                    return new JsonResponse($data);
                }
            }

        }
        //This is here to stop PHPStorm from complaining that there isn't a return statement. It should never be reached.
        return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
    }

    public function postAction(ServerRequestInterface $request, mysqli $connection){

        $table = $request->getAttribute('desiredTable',null);
        $date = $request->getAttribute('date',null);
        $module = $request->getAttribute('module',null);
        $user = $request->getAttribute('user',null);
        $accessed = $request->getAttribute('accessed',null);
        $type = $request->getAttribute('type',null);
        $action = $request->getAttribute('action',null);

        //Fetch the Module_ID for the module, which is the foreign key used in event_table.
        $sql = "SELECT Module_ID FROM modules_table WHERE Module_Name = '". $module ."'";
        $module_ID = $connection->query($sql)->fetch_row()[0];

        $sql = "INSERT INTO ".$table." (Date, Module, User, Accessed, Type, Action) VALUES ('".$date."', '".$module_ID."', '".$user."', '".$accessed."', '".$type."', '".$action."')";

        if ($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }
    }

    public function patchAction(ServerRequestInterface $request, mysqli $connection){

        $table = $request->getAttribute('desiredTable',null);
        $id = $request->getAttribute('Event_ID');
        $desiredColumn = $request->getAttribute('desiredColumn');
        $desiredValue = $request->getAttribute('desiredValue');

        $sql = "UPDATE ".$table." SET ".$desiredColumn."='".$desiredValue."' WHERE Event_ID = '".$id."'";

        if($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }
    }

    public function putAction(ServerRequestInterface $request, mysqli $connection){

        $table = $request->getAttribute('desiredTable',null);
        $event_id = $request->getAttribute('Event_ID');

        for($i = 0; $i < 6; $i++){
            $desiredColumn[$i] = $request->getAttribute('desiredColumn'.$i);
            $desiredValue[$i] = $request->getAttribute('desiredValue'.$i);
        }

        $sql = "UPDATE ".$table." SET ".$desiredColumn[0]."='".$desiredValue[0]."', ".$desiredColumn[1]."='".$desiredValue[1] ."', ".$desiredColumn[2]."='".$desiredValue[2] ."', ".$desiredColumn[3]."='".$desiredValue[3] ."', ".$desiredColumn[4]."='".$desiredValue[4] ."', ".$desiredColumn[5]."='".$desiredValue[5] ."' WHERE Event_ID = '".$event_id."'";

        if($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }
    }

    public function deleteAction(ServerRequestInterface $request, mysqli $connection){

        $table = $request->getAttribute('desiredTable',null);
        $desiredValue = $request->getAttribute('desiredValue');

        $sql = "DELETE FROM ".$table." WHERE Event_ID = '".$desiredValue."'";

        if ($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }

    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        mysqli_report(MYSQLI_REPORT_STRICT);

        $servername = "mysql";
        $username = "Alex";
        $pass ="password";
        $databasename ="my_database";

        //Create a connection
        $connection = new mysqli($servername,$username,$pass,$databasename);

        switch ($request->getMethod()){
            case 'GET':
                return $this->getAction($request,$connection);
            case 'POST':
                return $this->postAction($request,$connection);
            case 'PATCH':
                return $this->patchAction($request,$connection);
            case 'PUT':
                return $this->putAction($request,$connection);
            case 'DELETE':
                return $this->deleteAction($request,$connection);

            default:
                $connection->close();
                return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }
}