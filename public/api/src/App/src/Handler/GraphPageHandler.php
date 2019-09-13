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

class GraphPageHandler implements RequestHandlerInterface
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

    public function postAction(ServerRequestInterface $request, mysqli $connection){

        //Process JSON payload
        $data = $request->getBody()->__toString(); //This is the uploaded file in String format
        $decodedData = @json_decode($data);//This is the file in an array

        //Extract from dataset
        foreach($decodedData[0] as $d){
            $desiredColumn[] = $d[1];
            $desiredValue[] = $d[2];
        }

        //Check connection was successful
        if($connection->connect_error){
            return new JsonResponse($connection->connect_error);
        }
        else{

            //iterator for output
            $iter = 0;
            //Table container to be passed to template
            $t = null;

            for($i = 0; $i < sizeof($desiredColumn); $i++){
                if($desiredValue[$i] == "undefined" || $desiredValue[$i] == null){

                }
                else{

                    //Convert column names to the format needed by the SQL query
                    if($desiredColumn[$i] == "Module"){
                        $desiredColumn[$i] = "mt.Module_Name";
                    }
                    else if($desiredColumn[$i] == "User"){
                        $desiredColumn[$i] = "ut.User_Name";
                    }
                    else if($desiredColumn[$i] == "Accessed"){
                        $desiredColumn[$i] = "at.Accessed_Name";
                    }
                    else if($desiredColumn[$i] == "Type"){
                        $desiredColumn[$i] = "type";
                    }
                    if($desiredColumn[$i] !== "Event_ID"){
                        $altDesiredValue = $desiredValue[$i];
                    }

                    //Create query
                    $result = null;
                    $sql = "SELECT count(et.Event_ID), date(et.Date) as 'Date'
                            FROM event_table AS et 
                            JOIN accessed_table AS at ON et.Accessed = at.Accessed_ID 
                            JOIN modules_table AS mt on at.Module_ID = mt.Module_ID
                            JOIN users_table ut on et.User = ut.User_ID
                            WHERE ".$desiredColumn[$i]." LIKE '".$altDesiredValue."'
                            GROUP BY date(et.Date)
                            ORDER BY date(et.Date)
                            ";

                    $result = $connection->query($sql);

                    //Check there is data present
                    if($result->num_rows > 0){

                        //For each row in the table
                        while($row = $result->fetch_assoc()){
                            $t[$iter]['type'] = $desiredValue[$i];
                            $t[$iter]['date'] = $row['Date'];
                            $t[$iter]['events'] = $row['count(et.Event_ID)'];
                            $iter++;
                        }
                    }
                    else{
                        if($connection->error){
                            return new JsonResponse($connection->error);
                        }
                        $connection->close();
                        $data = null;
                        return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
                    }
                }

            }
            $data = $t;
            $connection->close();
            return new JsonResponse($data);
        }
    }



    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        mysqli_report(MYSQLI_REPORT_STRICT);

        $servername = "mysql";
        $username = "Alex";
        $pass ="password";
        $databasename ="analytics_database";

        //Create a connection
        $connection = new mysqli($servername,$username,$pass,$databasename);

        switch ($request->getMethod()){
            case 'POST':
                return $this->postAction($request,$connection);
            default:
                $connection->close();
                return new EmptyResponse(StatusCodeInterface::STATUS_FORBIDDEN);
        }
    }
}