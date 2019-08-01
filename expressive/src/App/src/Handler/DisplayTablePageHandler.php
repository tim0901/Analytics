<?php

declare(strict_types=1);

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use http\Env\Response;
use mysqli;
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

    public function indexAction(ServerRequestInterface $request, mysqli $connection){

        //Check connection was successful
        if($connection->connect_error){
            $data['error'] = ("MySQL connection failed. " . $connection->connect_error);
        }
        else{

            //Select table
            $sql = "SELECT id, firstname, lastname, email FROM my_table";
            $result = $connection->query($sql);

            //Check there is data present
            if($result->num_rows > 0){

                //Table container to be passed to template
                $t = null;
                $i = 0;
                //Output all rows of the table
                while($row = $result->fetch_assoc()){
                    $t[$i]['id'] = $row['id'];
                    $t[$i]['firstname'] = $row["firstname"];
                    $t[$i]['lastname'] = $row["lastname"];
                    $t[$i]['email'] = $row["email"];
                    $i++;
                }
                $data['table'] = $t;
            }
            else{
                $data['error'] = 'No data.';
            }
            $connection->close();
        }

        return new HtmlResponse($this->template->render('app::table-display-template', $data));
    }

    public function getAction(ServerRequestInterface $request, mysqli $connection){

        $desiredID = $request->getAttribute('id');

        //Check connection was successful
        if($connection->connect_error){
            $data['error'] = ("MySQL connection failed. " . $connection->connect_error);
        }
        else{

            //Select table
            $sql = "SELECT id, firstname, lastname, email FROM my_table";
            $result = $connection->query($sql);

            //Check there is data present
            if($result->num_rows > 0){

                //Table container to be passed to template
                $t = null;
                $i = 0;

                //For each row in the table
                while($row = $result->fetch_assoc()){

                    //If it is the correct entry,
                    if($row["id"] === $desiredID){
                        $t[$i]['id'] = $row['id'];
                        $t[$i]['firstname'] = $row["firstname"];
                        $t[$i]['lastname'] = $row["lastname"];
                        $t[$i]['email'] = $row["email"];
                        $i++;
                    }
                }

                $data['table'] = $t;

            }
            else{
                $data['error'] = 'No data.';
            }
            $connection->close();
        }

        return new HtmlResponse($this->template->render('app::table-display-template', $data));
    }

    public function putAction(ServerRequestInterface $request, mysqli $connection){
        //TODO putAction
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        mysqli_report(MYSQLI_REPORT_STRICT);

        $servername = "mysql";
        $username = "Alex";
        $pass ="password";
        $databasename ="my_database";

        //Create container for data to be sent to the template
        $data = [];

        //Create a connection
        $connection = new mysqli($servername,$username,$pass,$databasename);



        switch ($request->getAttribute('action','index')){
            case 'index':
                return $this->indexAction($request,$connection);
            case 'get':
                return $this->getAction($request,$connection);
            case 'put':
                return $this->putAction($request,$connection);
            default:

                $connection->close();
                return new HtmlResponse($this->template->render('app::info-template'));

               // return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }
}