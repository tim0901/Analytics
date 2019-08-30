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
use function Sodium\add;

class BatchUploadPageHandler implements RequestHandlerInterface
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

    //Insert unique users into users_table for more efficient upload
    public function insertUniques(ServerRequestInterface $request, mysqli $connection){

        //Process JSON payload
        $data = $request->getBody()->__toString(); //This is the uploaded file in String format
        $decodedData = @json_decode($data);//This is the file in an array

        //Fetch module name from URL
        $moduleName = $request->getAttribute('moduleName');

        //Insert module into modules_table
        $sql = "INSERT INTO modules_table (Module_Name) VALUES ('".$moduleName ."')";
        $connection->query($sql);

        //Fetch the Module_ID for the module, which is the foreign key used in event_table.
        $sql = "SELECT Module_ID FROM modules_table WHERE Module_Name = '". $moduleName ."'";
        $module_ID = $connection->query($sql)->fetch_row()[0];

        //Get a list of unique names in the data

        //Init arrays
        $uniqueNames = array();
        $uniqueAccessed = array();

        //Extract from dataset
        foreach($decodedData[0] as $d){
            $uniqueNames[] = $d[1];
            $uniqueAccessed[] = $d[3];
        }

        //Eliminate duplicates
        $uniqueNames = array_unique($uniqueNames);
        $uniqueAccessed = array_unique($uniqueAccessed);

        //Now hash the names
        foreach ($uniqueNames as &$u) {
            $u = sha1($u);
        }

        //Now insert them into the corresponding tables
        $sql = "INSERT INTO users_table (User_Name) VALUES ( '" . implode("'),('", $uniqueNames) . "')";
        $connection->query($sql);
        $sql = "INSERT INTO accessed_table (Module_ID, Accessed_Name) VALUES ( '" . $module_ID . "' , '" . implode("'),('" . $module_ID . "','", $uniqueAccessed) ."' )";
        $connection->query($sql);

        return $decodedData;
    }


    //Batch upload
    public function postAction(ServerRequestInterface $request, mysqli $connection){

        //Couldn't get JSON_TABLE insertion to work
        //$sql = "INSERT INTO event_table (Date,User,Affected_User,Accessed,Type,Action,Description,Origin,IP) SELECT * FROM JSON_TABLE (" . $decodedData . ", '$[0]' COLUMNS( Date VARCHAR(30) PATH '$.[0]',User VARCHAR(10) PATH '$.[1]', Affected_User VARCHAR(100) PATH '$.[2]', Accessed VARCHAR(100) PATH '$.[3]', Type VARCHAR(30) PATH '$.[4]', Action VARCHAR(100) PATH '$.[5]', Description VARCHAR(200) PATH '$.[6]',Origin VARCHAR(10) PATH '$.[7]', IP VARCHAR(20) PATH '$.[8]')) AS jt1";

        //Decode payload and insert unique elements into their respective tables
        $decodedData =  $this->insertUniques($request, $connection);

        //Insert the events into event_table
        $sql = "INSERT INTO event_table (Date, User, Accessed, Type, Action) VALUES ";

        for($i = 0; $i < sizeof($decodedData[0]); $i++){
            //Filter out automated system statements.
            if($decodedData[0][$i][1] == 'Moodle Support' || $decodedData[0][$i][1] == '-'){
                //SKIP ME
            }
            else{
                //Hash the user's name
                $user_hash = sha1($decodedData[0][$i][1]);

                //Fetch the User_ID that corresponds to the user_hash, which is the foreign key used in event_table.
                $userQuery = "SELECT User_ID FROM users_table WHERE User_Name = '". $user_hash ."'";
                $user_ID = $connection->query($userQuery)->fetch_row()[0];

                //Fetch the Accessed_ID that corresponds to the accessed column.
                $accessedQuery = "SELECT Accessed_ID FROM accessed_table WHERE Accessed_Name = '". $decodedData[0][$i][3] ."'";
                $accessed_ID = $connection->query($accessedQuery)->fetch_row()[0];

                //Convert the date to the correct format

                //Moodle doesn't output dates in a reliable format. The 'days' part may only have one digit. If this is the case, we need to append a 0.
                if(strlen($decodedData[0][$i][0]) !== 15){
                    $decodedData[0][$i][0] = "0" . $decodedData[0][$i][0];
                }

                //Now rearrange the date into the correct format for MySQL
                $transformedDate = "20" . substr($decodedData[0][$i][0],6,2) . "/" . substr($decodedData[0][$i][0],3,2) . "/" . substr($decodedData[0][$i][0],0,2) . " ". substr($decodedData[0][$i][0],10,5);

                //If this isn't the first element of the query, add a comma.
                if($i != 0){
                    $sql = $sql . ",";
                }
                //Add element to query
                $sql = $sql . "('".addslashes($transformedDate)."','".$user_ID."','".$accessed_ID."','".addslashes($decodedData[0][$i][4])."','".addslashes($decodedData[0][$i][5])."')";

            }
        }

        if ($connection->query($sql) === true){
            return new EmptyResponse(StatusCodeInterface::STATUS_OK);
        }
        else{
            return new JsonResponse($sql); //If it doesn't work, send me the SQl request so I can work out why
            return new EmptyResponse(StatusCodeInterface::STATUS_IM_A_TEAPOT);
        }

    }

    //Delete all entries in database for a given module.

    public function deleteAction(ServerRequestInterface $request, mysqli $connection){

        $moduleName = $request->getAttribute('moduleName');

        $sql = "DELETE FROM modules_table WHERE Module_Name = '".$moduleName."'";

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
        $databasename ="analytics_database";

        //Create a connection
        $connection = new mysqli($servername,$username,$pass,$databasename);

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