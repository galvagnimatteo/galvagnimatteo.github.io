<?php
session_start();
require_once '../utils/SingletonDB.php';
$now = time();
    if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['discard_after'] = $now+200;
if(!isset($_SESSION['admin'])||!$_SESSION['admin']){
    echo '{"status":"sessione non autenticata come amministratore"}';
    exit();
}
$db = SingletonDB::getInstance();
$reply=new \stdClass();
$reply->status="none";
$connection=$db->getConnection();
$connection->begin_transaction();
if (isset($_POST['action'])&&$_POST['action']=='insert') 
{

    if(isset($_POST['film']) &&
            isset($_POST['sala']) &&
            isset($_POST['Giorno']) 
            )
        {
            
        $query =
            'INSERT INTO Proiezione ( Data,IDFilm,NumeroSala) VALUES (?,?,?)';
        $preparedQuery = $connection->prepare($query);
        $preparedQuery->bind_param(
            'sss',
            $Data,
            $IDFilm,
            $NumeroSala
        );

        $IDFilm = $_POST['film'];
        $NumeroSala = $_POST['sala'];
        $Data = $_POST['Giorno'];

        $res=$preparedQuery->execute();        
        $preparedQuery->close();
        if($res){
            $reply->status="ok";
        }
        else{
            $reply->status="errore interno";
        }
    }
    else {
        $reply->status="parametri insufficenti";
	}
}else{
    if (isset($_POST['action'])&&$_POST['action']=='delete'){
        $id = $_POST['idproiezione'];
        $query =
            'delete FROM Proiezione where ID=?;';
        $preparedQuery = $connection->prepare($query);
        $preparedQuery->bind_param(
            's',
            $id
        );

        $res=$preparedQuery->execute();
        if($res){
            $reply->status="ok";
        }
        else{
            $reply->status="errore interno";
        }
        $preparedQuery->close();
    }
    
}
$proiezioni;
$resultproiezioni = $connection
    ->query('SELECT Data,Proiezione.ID as ID,IDFilm,Titolo ,NumeroSala FROM Proiezione,Film WHERE Film.ID=Proiezione.IDFilm');
    $connection->commit();//la transazione assicura che la lettura avvenga dopo gli inserimenti
$db->disconnect();
$i=0;
while ($row = $resultproiezioni->fetch_assoc()) { 
    $proiezione=new \stdClass();
    $proiezione->data=$row['Data'];
    $proiezione->id=$row['ID'];
    $proiezione->idfilm=$row['IDFilm'];
    $proiezione->titolofilm=$row['Titolo'];
    $proiezione->numeroSala=$row['NumeroSala'];
    $proiezioni[$i]=$proiezione;
    $i++;
}
$reply->proiezioni=$proiezioni;
echo json_encode($reply);

?>
