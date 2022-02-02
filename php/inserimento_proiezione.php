<?php
require_once "SingletonDB.php";

session_start();
$now = time();
if (isset($_SESSION["discard_after"]) && $now > $_SESSION["discard_after"]) {
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION["discard_after"] = $now + 400;


if(!isset($_SESSION["admin"])||!$_SESSION["admin"]){
    header("Location: ./admin.php");
    exit();
}




$document = file_get_contents("../html/template.html"); //load template
$content = file_get_contents("../html/admin_inserimento_proiezione_content.html"); //load content

$document = str_replace('<PAGETITLE>', 'inserimento Proiezione - PNG Cinema', $document);
$document = str_replace('<KEYWORDS>', '', $document);
$document = str_replace(
    "<BREADCRUMB>",
    '<a href="home.php">Home</a> / <a href="admin.php">amministrazione</a>/inserimento Proiezione',
    $document
);

$db = SingletonDB::getInstance();

if (isset($_GET["action"])) 
{
    $action = $_GET["action"];
    if($action=="insert")
    {
        if(isset($_POST["film"]) &&
            isset($_POST["sala"]) &&
            isset($_POST["Giorno"]) 
            )
        {
            
            $query =
                "INSERT INTO Proiezione ( Data,IDFilm,NumeroSala) VALUES (?,?,?)";
            $preparedQuery = $db->getConnection()->prepare($query);
            $preparedQuery->bind_param(
                "sss",
                $Data,
                $IDFilm,
                $NumeroSala
            );

            $IDFilm = $_POST["film"];
            $NumeroSala = $_POST["sala"];
            $Data = $_POST["Giorno"];

            $res=$preparedQuery->execute();
            /*$db->disconnect();*/
            $preparedQuery->close();
            if($res){
                $content=str_replace("<STATUS>", "<p class='success'>inserimento avvenuto correttamente</p>", $content);
            }
            else{
                $content=str_replace("<STATUS>", "<p class='failure'>errore nell'inserimento prego riprovare</p>", $content);
            }
        }
    }
}
else{
    $content=str_replace("<STATUS>", "", $content);
}
$query ="select ID,Titolo from Film ";
$preparedQuery = $db->getConnection()->prepare($query);
$preparedQuery->execute();
$films=$preparedQuery->get_result();

$stringfilms="";
while($row = $films->fetch_assoc()){
    $stringfilms=$stringfilms."<option value=".$row["ID"].">".$row["Titolo"]."</option>";
}
$content = str_replace("<FILM>", $stringfilms, $content);
$document = str_replace("/php/inserimento_proiezione.php", "#", $document);
$document = str_replace("<CONTENT>", $content, $document);

echo $document;

?>
