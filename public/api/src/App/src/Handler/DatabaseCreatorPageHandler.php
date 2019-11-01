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
use function Sodium\add;

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

        //Create database

        $sql = "CREATE DATABASE analytics_database"; //This has been done already by docker, and so isn't necessary

        $sql = "USE analytics_database";
        $connection->query($sql);

        //Create tables - definitions auto-generated from PHPStorm
        $sql = "
        create table if not exists modules_table
        (
            Module_ID   int auto_increment
                primary key,
            Module_Name varchar(100) null,
            constraint modules_table_Module_Name_uindex
                unique (Module_Name)
        );";
        $connection->query($sql);

        $sql = "
        create table if not exists accessed_table
        (
            Accessed_ID   int auto_increment
                primary key,
            Module_ID     int          null,
            Accessed_Name varchar(100) null,
            constraint accessed_table_Accessed_Name_uindex
                unique (Accessed_Name),
            constraint module_foreign_key_at
                foreign key (Module_ID) references modules_table (Module_ID)
                    on delete cascade
        );";
        $connection->query($sql);

        $sql = "create table if not exists users_table
        (
            User_ID   int auto_increment
                primary key,
            User_Name varchar(100) null,
            constraint users_table_User_Name_uindex
                unique (User_Name)
        );";
        $connection->query($sql);

        $sql = "create table if not exists event_table
        (
            Event_ID int auto_increment
                primary key,
            Date     datetime     null,
            User     int          null,
            Accessed int          null,
            Type     varchar(30)  null,
            Action   varchar(100) null,
            constraint accessed_foreign_key
                foreign key (Accessed) references accessed_table (Accessed_ID)
                    on delete cascade,
            constraint user_foreign_key
                foreign key (User) references users_table (User_ID)
                    on delete cascade
        );";

        if ($connection->query($sql) === true){
            $connection->close();
            return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
        }
        else{
            return new JsonResponse($connection->error); //If it doesn't work, send me the SQl request so I can work out why
        }
    }

    //Drop all tables in the database!!! This is only called via Postman
    public function deleteAction(ServerRequestInterface $request, mysqli $connection){

        $sql = "DROP TABLE event_table";
        $connection->query($sql);

        $sql = "DROP TABLE users_table";
        $connection->query($sql);

        $sql = "DROP TABLE accessed_table";
        $connection->query($sql);

        $sql = "DROP TABLE modules_table";

        if ($connection->query($sql) === true){
            $connection->close();
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            $connection->close();
            return new EmptyResponse(StatusCodeInterface::STATUS_BAD_REQUEST);
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
        $connection = new mysqli($servername,$username,$pass, $databasename);

        switch ($request->getMethod()){
            case 'POST':
                return $this->postAction($request,$connection);
            case 'DELETE':
                return $this->deleteAction($request,$connection);

            default:
                $connection->close();
                return new EmptyResponse(StatusCodeInterface::STATUS_FORBIDDEN);
        }
    }
}




