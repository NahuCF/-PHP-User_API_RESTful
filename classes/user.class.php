<?php

class User
{
    private $conection;

    public function __construct($db_config)
    {
        try
        {
            $this->conection = new PDO("mysql:host=localhost;dbname=" . $db_config["db_name"], $db_config["user"], $db_config["password"]);
        }
        catch(PDOException $e)
        {
            $this->response(500);
        }
    }

    public function API()
    {
        if(http_response_code() !== 500)
        {
            header("Content-Type: application/json");

            switch($_SERVER["REQUEST_METHOD"])
            {
                case "GET":
                    if(isset($_GET["ID"]))
                        $this->getUser($_GET["ID"]);
                    else
                        $this->getUsers();
                    break;
                case "POST":
                    $this->createUser();
                    break;
                case "PUT":
                    $this->updateUser();
                    break;
                case "DELETE":
                    $this->deleteUser();
                    break;
                default:
                    $this->response(405);
                    break;
            }
        }
    }

    private function getUser($user_id)
    {
        if(!empty($_GET["ID"]))
        {
            $statement =  $this->conection->prepare("SELECT * FROM users WHERE ID = :user_id LIMIT 1");
            $statement->execute(array("user_id" => $this->cleanString($user_id)));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            
            if(empty($result))
                $this->response(404);
            else
                $this->response(200, $result);
        }
        else
        {
            $this->response(400);
        }
    }

    private function getUsers()
    {
        $statement =  $this->conection->query("SELECT * FROM users");
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        $this->response(200, $result);
    }

    private function createUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Check if the input data is not empty and if the email is valid
        if(!empty($data["password"]) && !empty($data["email"]) && !empty($data["password"]) && filter_var($this->cleanString($data["email"]), FILTER_VALIDATE_EMAIL))
        {
            $statement = $this->conection->prepare("INSERT INTO users values(null, :name, :email, :password)");
            $statement->execute(
                array(
                    "name"      => $this->cleanString($data["name"]),
                    "email"     => $this->cleanString($data["email"]),
                    "password"  => $this->cleanString($data["password"])
                )
            );
            
            $this->response(200);
        }
        else
        {
            $this->response(400);
        }
    }

    private function updateUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(isset($_GET["ID"]) && !empty($_GET["ID"]))
        {
            // Check if the input data is not empty and if the email is valid
            if(!empty($data["password"]) && !empty($data["email"]) && !empty($data["password"]) && filter_var($this->cleanString($data["email"]), FILTER_VALIDATE_EMAIL))
            {
                $statement =  $this->conection->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE ID = :user_id LIMIT 1");
                $statement->execute(
                    array(
                        "name"      => $this->cleanString($data["name"]),
                        "email"     => $this->cleanString($data["email"]),
                        "password"  => $this->cleanString($data["password"]),
                        "user_id"   => $this->cleanString($_GET["ID"])
                    )
                );
                
                if($statement->rowCount() == 0)
                    $this->response(404);
                else
                    $this->response(200);
            }
            else
            {
                $this->response(400);
            }
        }
        else
        {
            $this->response(400);
        }
    }

    private function deleteUser()
    {
        if(isset($_GET["ID"]) && !empty($_GET["ID"]))
        {
            $statement = $this->conection->prepare("DELETE FROM users WHERE ID = :user_id LIMIT 1");
            $statement->execute(array("user_id" => $this->cleanString($_GET["ID"])));

            if($statement->rowCount() == 0)
                $this->response(404);
            else
                $this->response(200);
        }
        else
        {
            $this->response(400);
        }
    }

    private function cleanString($word)
    {
        $word = trim($word);
        $word = stripslashes($word);
        $word = htmlspecialchars($word);

        return $word;
    }

    private function response($code_status, $array = '')
    {
        http_response_code($code_status);

        $status = array(  
            200 => "Ok",
            400 => "Bad Request",
            404 => "Not Found",  
            405 => "Method Not Allowed",
            500 => "Internal Server Error"
        ); 

        if(!empty($array))
        {
            $result =
            [
                "status" => $code_status,
                "message" => $status[$code_status]
            ];

            array_push($result, $array);
            
            echo json_encode($result);
        }
        else
        {
            echo json_encode(["status" => $code_status, "message" => $status[$code_status]]);
        }
    }
}

?>