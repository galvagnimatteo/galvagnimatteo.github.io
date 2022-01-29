<?php
require_once "SingletonDB.php";
require_once "utils/controls.php";

class Users
{

    function insert()
    {

        if (isset($_POST["name_register"]) && isset($_POST["username_register"]) && isset($_POST["password_register"]) && isset($_POST["email_register"]) && isset($_POST["surname_register"]) && isset($_POST["pass_register_confirm"]))
        {

            $username = $_POST["username_register"];
            $password = $_POST["password_register"];
            $name = $_POST["name_register"];
            $surname = $_POST["surname_register"];
            $email = $_POST["email_register"];

            $confirm_password = $_POST["pass_register_confirm"];

            $result = registerControls($username, $name, $surname, $email, $password, $confirm_password);

            if ($result == "OK")
            {

                $db = SingletonDB::getInstance();

                $query = "INSERT INTO Utente(Username,Email,Nome,Cognome,Password) VALUES (?,?,?,?,?)";
                $preparedQuery = $db->getConnection()
                    ->prepare($query);
                $preparedQuery->bind_param("sssss", $username, $email, $name, $surname, $password);

                $_SESSION["a"] = $username;

                $preparedQuery->execute();
                $db->disconnect();
                $preparedQuery->close();

            }

            return $result;

        }
    }

    function search()
    {

        if (isset($_POST["email_login"]) && isset($_POST["password_login"]))
        {

            $email = $_POST["email_login"];
            $password = $_POST["password_login"];

            $result = loginControls($email, $password);

            if ($result == "OK")
            {

                $db = SingletonDB::getInstance();
                $query = "SELECT Username FROM Utente WHERE Email=? AND Password=?";
                $preparedQuery = $db->getConnection()
                    ->prepare($query);
                $preparedQuery->bind_param("ss", $email, $password);

                $preparedQuery->execute();
                $resultCast = $preparedQuery->get_result();

                $db->disconnect();
                $preparedQuery->close();

                if ($resultCast->num_rows > 0)
                {
                    $row = $resultCast->fetch_assoc();
                    $_SESSION["a"] = $row["Username"];

                    header("location:home.php");
                }
                else
                {
                    unset($_SESSION["a"]);
                    header("location:area_utenti.php?action=login_page");
                }

            }

            return $result;
        }

    }

    function searchRegistered($email, $username, $value)
    {

        $db = SingletonDB::getInstance();
        $query = "SELECT * FROM Utente WHERE Email=?  OR Username=?";

        $preparedQuery = $db->getConnection()
            ->prepare($query);
        $preparedQuery->bind_param("ss", $email, $username);

        $preparedQuery->execute();
        $resultCast = $preparedQuery->get_result();

        if ($resultCast->num_rows > $value)
        {
            $db->disconnect();
            $preparedQuery->close();
            return true;
        }

        return false;

    }
    function getProfile()
    {
        if (isset($_SESSION["a"]))
        {
            $db = SingletonDB::getInstance();

            $query = "SELECT Username, Nome,Cognome,Password,Email FROM Utente WHERE Username=? ";
            $preparedQuery = $db->getConnection()
                ->prepare($query);
            $username = $_SESSION["a"];
            $preparedQuery->bind_param("s", $username);

            $preparedQuery->execute();
            $resultCast = $preparedQuery->get_result();
            $db->disconnect();
            $preparedQuery->close();
            $row = $resultCast->fetch_assoc();

            $home_content = file_get_contents("../html/items/updateProfile_content.html");
            $home_content = str_replace("<USERNAME>", $row["Username"], $home_content);
            $home_content = str_replace("<NOME>", $row["Nome"], $home_content);
            $home_content = str_replace("<COGNOME>", $row["Cognome"], $home_content);
            $home_content = str_replace("<EMAIL>", $row["Email"], $home_content);
            $home_content = str_replace("<PASSWORD>", $row["Password"], $home_content);
            if (isset($_GET["error"]))
            {
                $home_content = str_replace("<ERRORMESSAGE>", "Email/Username già registrati", $home_content);
                unset($_GET["error"]);
            }
            return $home_content;
        }
    }
    function deleteProfile()
    {
        $db = SingletonDB::getInstance();

        $query = "DELETE FROM Utente WHERE Username=?";
        if ($preparedQuery = $db->getConnection()
            ->prepare($query))
        {
            $preparedQuery->bind_param("s", $username);
            if (isset($_SESSION["a"]))
            {
                $username = $_SESSION["a"];
                $preparedQuery->execute();
                $db->disconnect();
                $preparedQuery->close();
                return true;
            }
            else
            {
                return false;
            }
        }
        return false;

    }

    function changeProfile()
    {
        if (isset($_POST["username_profile"]) && isset($_POST["name_profile"]) && isset($_POST["password_profile"]) && isset($_POST["email_profile"]) && isset($_POST["surname_profile"]) && isset($_POST["password_profile"]) && isset($_SESSION["a"]))
        {

            $newusername = $_POST["username_profile"];
            $oldusername = $_SESSION["a"];
            $password = $_POST["password_profile"];
            $name = $_POST["name_profile"];
            $surname = $_POST["surname_profile"];
            $email = $_POST["email_profile"];
            $confirm_password = $_POST["pass_profile_confirm"];

            $result = registerControls($newusername, $name, $surname, $email, $password, $confirm_password);
    
            if($result == "OK"){

                $db = SingletonDB::getInstance();

                $query = "UPDATE Utente SET Username=?, Nome=?, Cognome=?, Password=?,Email=? WHERE Username=?";
                if ($preparedQuery = $db->getConnection()
                    ->prepare($query))
                {
                    $preparedQuery->bind_param("ssssss", $newusername, $name, $surname, $password, $email, $oldusername);

                    $preparedQuery->execute();

                    $db->disconnect();
                    $preparedQuery->close();
                    $_SESSION["a"] = $newusername;
                }else{
                    header("location:500.php");
                    die();
                }
            }
            return $result;
        }
    }
}

?>
