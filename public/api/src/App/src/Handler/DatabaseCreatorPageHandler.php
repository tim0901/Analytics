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
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DatabaseCreatorPageHandler implements RequestHandlerInterface
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


    //Create database and tables.
    public function postAction(ServerRequestInterface $request, mysqli $connection){

        $table = $request->getAttribute('desiredTable',null);
        $date = $request->getAttribute('date',null);
        $user = $request->getAttribute('user',null);
        $affected_user = $request->getAttribute('affected_user',null);
        $accessed = $request->getAttribute('accessed',null);
        $type = $request->getAttribute('type',null);
        $action = $request->getAttribute('action',null);
        $description = $request->getAttribute('description',null);
        $origin = $request->getAttribute('origin',null);
        $ip = $request->getAttribute('ip',null);

        $sql = "INSERT INTO ".$table." (Date, User, Affected_User, Accessed, Type, Action, Description, Origin, IP) VALUES ('".$date."', '".$user."', '".$affected_user."', '".$accessed."', '".$type."', '".$action."', '".$description."', '".$origin."', '".$ip."')";

        if ($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }
    }

    //Edit database/tables?
    public function putAction(ServerRequestInterface $request, mysqli $connection){

        $table = $request->getAttribute('desiredTable',null);
        $event_id = $request->getAttribute('Event_ID');

        for($i = 0; $i < 9; $i++){
            $desiredColumn[$i] = $request->getAttribute('desiredColumn'.$i);
            $desiredValue[$i] = $request->getAttribute('desiredValue'.$i);
        }

        $sql = "UPDATE ".$table." SET ".$desiredColumn[0]."='".$desiredValue[0]."', ".$desiredColumn[1]."='".$desiredValue[1] ."', ".$desiredColumn[2]."='".$desiredValue[2] ."', ".$desiredColumn[3]."='".$desiredValue[3] ."', ".$desiredColumn[4]."='".$desiredValue[4] ."', ".$desiredColumn[5]."='".$desiredValue[5] ."', ".$desiredColumn[6]."='".$desiredValue[6] ."', ".$desiredColumn[7]."='".$desiredValue[7] ."', ".$desiredColumn[8]."='".$desiredValue[8] ."' WHERE Event_ID = '".$event_id."'";

        if($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }
    }

    //drop database.
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
            case 'POST':
                return $this->postAction($request,$connection);
            case 'PUT':
                return $this->putAction($request,$connection);
            case 'DELETE':
                return $this->deleteAction($request,$connection);

            default:
                $connection->close();
                return new EmptyResponse(StatusCodeInterface::STATUS_FORBIDDEN);
        }
    }
}