<?php
session_start();
require_once "utils/SingletonDB.php";
require_once "utils/prenotaPosti.php";
require_once "utils/mappaPosti.php";
require_once "utils/generaPagina.php";
require_once "utils/controlli.php";
//CheckSession($login_required, $admin_required);
CheckSession(false, false); //refresh della sessione se scaduta

if (
    !isset(
        $_POST["numTicketIntero"],
        $_POST["numTicketRidotto"],
        $_POST["modSelezPosti"],
        $_POST["idproiez"],
        $_POST["orario"],
        $_POST["seats"],
        $_POST["numSala"],
        $_POST["titoloFilm"],
        $_POST["dataIta"],
        $_POST["pint"],
        $_POST["prid"]
    )
) {
    header("Location: 404.php");
    die();
}

pulisci($_POST["orario"]);
pulisci($_POST["modSelezPosti"]);
pulisci($_POST["seats"]);
pulisci($_POST["dataIta"]);
pulisci($_POST["titoloFilm"]);

//verifiche
if (
    !(
        is_numeric($_POST["numTicketIntero"]) &&
        is_numeric($_POST["numTicketRidotto"]) &&
        is_numeric($_POST["idproiez"]) &&
        is_numeric($_POST["numSala"]) &&
        is_numeric($_POST["pint"]) &&
        is_numeric($_POST["prid"])
    )
) {

    header("Location: 404.php");
    die();
}

$username = ""; //se resta '' inserisce null e l'utente è un ospite
if (isset($_SESSION["a"])) {
    $username = $_SESSION["a"];
}

$totNumBiglietti =
    intval($_POST["numTicketIntero"]) + intval($_POST["numTicketRidotto"]);

$postiver = explode(",", $_POST["seats"]);

if ($totNumBiglietti > 4 || count($postiver) > 4) {
    header(
        "Location: prenotazione.php?idproiez=" .
            $_POST["idproiez"] .
            "&err_server1=1"
    );
    die();
}



$statoPosti = mappaPosti($_POST["numSala"], $_POST["idproiez"], $_POST["orario"]);

$postoOccupato = false;
foreach ($statoPosti as &$posto) {
	if ($statoPosti[$posto] == 1) { //se un posto è occupato
		$postoOccupato = true;
		break;
	}
}

if ($postoOccupato) {
	header(
		"Location: prenotazione.php?idproiez=" .
			$_POST["idproiez"] .
			"&err_server2=". $posto
	);
	unset($posto);
	die();
}
unset($posto);

if (count($postiver) < $totNumBiglietti) {
	header(
		"Location: prenotazione.php?idproiez=" .
			$_POST["idproiez"] .
			"&err_server3=1"
	);
	die();
}

$idPrenotaz = prenotaPosti(
	$postiver,
	$username,
	$_POST["idproiez"],
	$_POST["numSala"]
);

unset($statoPosti);

if ($idPrenotaz != -1) {
	generaPaginaConferma($postiver, $idPrenotaz, $totNumBiglietti);

} else {
	header("Location: 500.php");
	die();
}






function generaPaginaConferma($listaPostiFormat, $idPrenotaz, $totNumBiglietti)
{
    //pagina conferma
    $acquistoconferma_content = file_get_contents(
        "../html/conferma_acquisto.html"
    );

    //SESSION

    $acquistoconferma_content = str_replace(
        "<FILM-TITLE>",
        $_POST["titoloFilm"],
        $acquistoconferma_content
    );
    $acquistoconferma_content = str_replace(
        "<NUM-BIGLIETTI>",
        $totNumBiglietti,
        $acquistoconferma_content
    );
    $acquistoconferma_content = str_replace(
        "<NUM-BIGLIETTI-INT-RID>",
        "Intero: " .
            $_POST["numTicketIntero"] .
            " " .
            "Ridotto: " .
            $_POST["numTicketRidotto"],
        $acquistoconferma_content
    );

    $acquistoconferma_content = str_replace(
        "<PREN-DATA-ORA>",
        $_POST["dataIta"] . ", " . substr($_POST["orario"], 0, -3),
        $acquistoconferma_content
    );
    $acquistoconferma_content = str_replace(
        "<PREN-SALA>",
        $_POST["numSala"],
        $acquistoconferma_content
    );
    $acquistoconferma_content = str_replace(
        "<POSTI-LISTA>",
        strtoupper($listaPostiFormat),
        $acquistoconferma_content
    );
    $acquistoconferma_content = str_replace(
        "<COD-ACQ>",
        $idPrenotaz,
        $acquistoconferma_content
    );
    $acquistoconferma_content = str_replace(
        "<TOT-SPESO>",
        floatval($_POST["pint"]) * intval($_POST["numTicketIntero"]) +
            floatval($_POST["prid"]) * intval($_POST["numTicketRidotto"]),
        $acquistoconferma_content
    );
    $title = "Conferma acquisto  " . $_POST["titoloFilm"] . " - PNG Cinema";
    $keywords = "Acquisto, biglietti, " . $_POST["titoloFilm"];
    $description =
        "pagina di conferma acquisto biglietti per " . $_POST["titoloFilm"];
    $breadcrumbs = "Conferma acquisto";
    $jshead =
        '<meta name="robots" content="noindex" follow /> ' . //lo attacco da qua perche non ho voglia di modificare tutto
        ' <script src="../js/promptonclose.js"></script>';
    //GeneratePage($page,$content,$breadcrumbs,$title,$description,$keywords,$jshead,$jsbody);
    echo GeneratePage(
        "Conferma",
        $acquistoconferma_content,
        $breadcrumbs,
        $title,
        $description,
        $keywords,
        $jshead,
        ""
    );
}
?>
