<?php

//$postistr: stringa nella forma xy, x1y1, x2y2 dove x{1,2,3} è al fila e y{1,2,3} è il numero del posto

function prenotaPosti($postistr, $username, $idproiez, $orario, $numSala)
{
    $idprenot = -1;

    if ($postistr != "") {
        $posti = explode(",", $postistr);
        $totNumBiglietti = count($posti);

        $db = SingletonDB::getInstance();
        $db->connect();

        $preparedQuery = $db
            ->getConnection()
            ->prepare(
                "INSERT INTO Prenotazione(NumeroPersone, UsernameUtente, IDProiezione, OraProiezione)" .
                    "VALUES (?, NULLIF(?, ''), ?, ?)"
            );
        $preparedQuery->bind_param(
            "isis",
            $totNumBiglietti,
            $username,
            $idproiez,
            $orario
        );
        $res = $preparedQuery->execute();

        $idprenot = mysqli_insert_id($db->getConnection());
        $db->disconnect();
        if ($res) {
            foreach ($posti as $posto) {
                $numPosto = intval(substr($posto, 1));
                $fila = strtoupper(substr($posto, 0, 1));
                $db->connect();
                $preparedQuery2 = $db
                    ->getConnection()
                    ->prepare(
                        "INSERT INTO Partecipa(NumeroPosto, FilaPosto, NumeroSala, IDPrenotazione)" .
                            "VALUES (?, ?, ?, ?)"
                    );
                $preparedQuery2->bind_param(
                    "isii",
                    $numPosto,
                    $fila,
                    $numSala,
                    $idprenot
                );
                $res2 = $preparedQuery2->execute();
                $db->disconnect();

                if (!$res2) {
                    return -1;
                }
            }
        }
    }
    return $idprenot;
}

?>
