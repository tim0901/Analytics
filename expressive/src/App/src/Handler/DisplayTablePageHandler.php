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

        $desiredColumn = $request->getAttribute('desiredColumn','id');
        $desiredValue = $request->getAttribute('desiredValue');

        //Check connection was successful
        if($connection->connect_error){
            $data['error'] = ("MySQL connection failed. " . $connection->connect_error);
        }
        else{

            //Filter table for desired results, or show all
            if($desiredValue !== null){
                $sql = "SELECT id, firstname, lastname, email FROM my_table WHERE ".$desiredColumn."='".$desiredValue."'";
            }
            else{
                $sql = "SELECT id, firstname, lastname, email FROM my_table ";
            }

            $result = $connection->query($sql);

            //Check there is data present
            if($result->num_rows > 0){
                //Table container to be passed to template
                $t = null;
                $i = 0;

                //For each row in the table
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

    public function postAction(ServerRequestInterface $request, mysqli $connection){

        $firstname = $request->getAttribute('firstname',null);
        $lastname = $request->getAttribute('lastname',null);
        $email = $request->getAttribute('email',null);

        $sql = "INSERT INTO my_table (firstname, lastname, email) VALUES ('".$firstname."', '".$lastname."', '".$email."')";

        if ($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }
    }

    public function patchAction(ServerRequestInterface $request, mysqli $connection){

        $id = $request->getAttribute('id');
        $desiredColumn = $request->getAttribute('desiredColumn');
        $desiredValue = $request->getAttribute('desiredValue');

        $sql = "UPDATE my_table SET ".$desiredColumn."='".$desiredValue."' WHERE id = '".$id."'";

        if($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }

    }

    public function deleteAction(ServerRequestInterface $request, mysqli $connection){

        $desiredValue = $request->getAttribute('desiredValue');

        $sql = "DELETE FROM my_table WHERE id = '".$desiredValue."'";

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
            case 'DELETE':
                return $this->deleteAction($request,$connection);

            default:
                $connection->close();
                return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }
}