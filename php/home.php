<?php

    include 'SingletonDB.php';
    include 'mostra_errori.php';

    $document = file_get_contents('../html/template.html');
    $home_content = file_get_contents('../html/home_content.html');
    $quickpurchase_films = "";
    $cards = "";

    //Query per inserimento film (anche acquisto rapido)------------------------

    $db = SingletonDB::getInstance();
    $resultFilms = $db->getConnection()->query("SELECT * FROM Film ORDER BY DataUscita");
    $db->disconnect();

    //--------------------------------------------------------------------------

    if(!empty($resultFilms) && $resultFilms->num_rows > 0){

        $card_home_template = file_get_contents('../html/items/card-home.html');

        while($row = $resultFilms->fetch_assoc()) {

            $quickpurchase_films = $quickpurchase_films . '<option value="' . $row["ID"] . '">' . $row["Titolo"] . '</option>';

            //Query per dati film ----------------------------------------------

            $db->connect();

            $preparedQuery = $db->getConnection()->prepare("SELECT * FROM CastFilm INNER JOIN Afferisce on (CastFilm.ID = Afferisce.IDCast) INNER JOIN Film on (Afferisce.IDFilm = Film.ID) WHERE Film.ID = ?");
            $preparedQuery->bind_param('i', $row["ID"]);
            $preparedQuery->execute();
            $resultCast = $preparedQuery->get_result();

            $db->disconnect();

            $preparedQuery->close();

            //------------------------------------------------------------------

            $card_home_item = $card_home_template;

            $card_home_item = str_replace('<FILMTITLE>', $row["Titolo"], $card_home_item);

            $director = "";
            $cast = "";

            while($rowCast = $resultCast->fetch_assoc()) {

                if($rowCast["Ruolo"] == "R"){

                    if($director == ""){

                        $director = $rowCast["Nome"] . " " . $rowCast["Cognome"];

                    }else{

                        $director = $director . ", " . $rowCast["Nome"] . " " . $rowCast["Cognome"];

                    }

                }else{

                    if($cast == ""){

                        $cast = $rowCast["Nome"] . " " . $rowCast["Cognome"];

                    }else{

                        $cast = $cast . ", " . $rowCast["Nome"] . " " . $rowCast["Cognome"];

                    }
                }

            }

            $card_home_item = str_replace('<FILMDIRECTOR>', $director, $card_home_item);
            $card_home_item = str_replace('<FILMCAST>', $cast, $card_home_item);

            $cards = $cards . $card_home_item;

        }

    }else{

        $cards = '<p class="error">Nessun film trovato, ci scusiamo del disagio.</p>';

    }

    $document = str_replace('<PAGETITLE>', 'Home - PNG Cinema', $document);
    $document = str_replace('<KEYWORDS>', 'ultime uscite, acquisto, acquisto rapido', $document);
    $document = str_replace('<DESCRIPTION>', 'Pagina principale: è possibile consultare le ultime uscite in programmazione e acquistare rapidamente un biglietto.', $document);
    $document = str_replace('<BREADCRUMB>', '<a href="home.php">Home</a> / ', $document);

    $document = str_replace('<JAVASCRIPT-HEAD>', '<script type="text/javascript" src="../js/carousel.js"> </script>', $document);
    $document = str_replace('<JAVASCRIPT-BODY>', '<script type="text/javascript" src="../js/jquery-3.6.0.min.js"> </script>
                            <script type="text/javascript" src="../js/quickpurchase.js"> </script>', $document);

    $home_content = str_replace('<FILM-OPTIONS>', $quickpurchase_films, $home_content);
    $home_content = str_replace('<CARDS-HOME>', $cards, $home_content);

    $document = str_replace('<CONTENT>', $home_content, $document);

    echo($document);

?>