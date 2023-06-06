<?php
/*
    Author: Harald Popke  (h.popke@dshs-koeln.de)
    
    Dieses Skript setzt bei Benutzern das Passwort für den Identity-Service und die Selbstverbucher-Pin auf TTMMJJJJ
    Die Pin für den Selbstverbucher kann sowohl bei internen als auch externen Benutzern in Alma gesetzt werden.
    Das Password für den Identity-Service kann nur bei internen Benutzern in Alma gesetzt werden.
    Soll nur die Pin für die Selbstverbucher gesetzt werden, muss die Zeile:
    $data['password'] = $new_password;
    auskommentiert werden.
    Das Skript kann leicht angepasst werden, um auch andere Daten in einem Benutzersatz zu ändern. 

    Für individuelle Anpassungen oder falls Sie das Skript von einem Dienstleister ausführen lassen wollen, 
    wenden Sie sich bitte an die Harald Popke DV-Systeme GmbH:
    harald.popke@hp-dv-systeme.de

    Das Skript darf frei verwendet und geändert werden. Es unterliegt keinen Nutzungsbedingungen. 
    
    !!Die Ausführung erfolgt auf eigene Gefahr!! 

    Das Skript gibt pro bearbeiteten Benutzer eine Rückmeldung mit einer fortlaufenden Nummer:
    
    Beispiel:
     1 - B41$0000009855 : Setze Kennwort und Pin auf 01121990";
     2 - B41$0000066894 : Setze Kennwort und Pin auf 12122000";
     3 - B41$0000015696 : hat kein Geburtsdatum und wird übersprungen!";

    Sollte das Skript unterbrochen werden, kann so sehr einfach die user_ids.txt um die bereits bearbeiten Fälle gekürzt werden.

    Aufruf des Skriptes: Einfach auf der Konsole "php alma-pin.php" eingeben.

*/

// Individueller API-Key für den lesenden und schreibenden Zugriff auf die USER-API von Alma
$api_key = "XXX";


/* Dateipfad zur zu lesenden Datei, dort muss pro Zeile eine Benutzernummer stehen.
    Zum Beispiel:
    B41$0000009855
    B41$0000066894
    B41$0000015696

    Diese Datei läßt sich sehr einfach direkt in Alma erstellen:
    1. Öffne die erweiterte Suche
    2. Suche in "Benutzer" nach den Benutzergruppen, dessen Daten geändert werden sollen
    3. In der Ergebnisliste drücke ganz rechts auf "Export-Liste" und führe einen "erweiterten Export" aus.
    4. Es wird eine Excel-Datei runtergeladen, welche in der ersten Spalte die Benutzernummern beinhaltet.
    5. Markiere alle Benutzernummern und kopiere sie in eine Textdatei. Diese dient dann als Eingabe für dieses Skript.
    6. Kopiere diese Textdatei in das selbe Verzeichnis, wo das Skript liegt. Benenne diese user_ids.txt
*/
$dateipfad = 'user_ids.txt';

// Überprüfe, ob die Datei existiert und lesbar ist
if (file_exists($dateipfad) && is_readable($dateipfad)) {
    // Öffne die Datei im Lesemodus
    $datei = fopen($dateipfad, 'r');
    
    // Schleife über jede Zeile der Datei
    $i = 1;
    while (($zeile = fgets($datei)) !== false) {
      // Entferne zur Sicherheit alle Whitespaces in der gelesenen Zeile
      $user_id = trim($zeile);
      
      // Aufruf der setPassword-Funktion mit der eingelesenen user_id und dem api_key
      $answer = setPassword($user_id,$api_key);

      // Ausgabe der Antwort der setPassword-Funktion mit einer laufenden Nummer als Prefix
      echo "$i - $answer\n";
      $i++;
    }
    
    // Schließe die Datei
    fclose($datei);
} else {
    echo "Die Datei konnte nicht geöffnet werden.";
}


function setPassword($user_id, $api_key){
    
    $url = "https://api-eu.hosted.exlibrisgroup.com/almaws/v1/users/$user_id?user_id_type=all_unique&view=full&expand=none&apikey=$api_key";
    // Optionen für den Request
    $options = array(
        'http' => array(
            'header'  => "Accept: application/json\r\n",
            'method'  => 'GET',
            'ignore_errors' => true
        )
    );

    // Erzeugen des Request-Kontexts
    $context = stream_context_create($options);

    // Senden des Requests und Speichern der JSON-Antwort
    $response = file_get_contents($url, false, $context);

     // Auswerten ob die Anfrage erfolgreich war
    if($http_response_header[0] != "HTTP/1.1 200 OK"){
        echo $response;
        return("$user_id : Fehler beim Abruf des Benutzers!");
    }

    // Decodieren der JSON-Antwort in eine Datenstruktur
    $data = json_decode($response, true);

    // DEBUG: Einkommentieren, um die empfangenen Daten zu sehen
    // print_r($data);

    // Überspringe den Benutzer, wenn er kein Datum gesetzt hat.
    if(!isset($data['birth_date']) || empty($data['birth_date'])){
        return "$user_id : hat kein Geburtsdatum und wird übersprungen!\n";
    }

    // Falls ein Datum vorhanden ist, sieht es zum Beispiel so aus: 1986-09-07Z
    $birth_date = $data['birth_date'];

    // Entferne das Z
    $birth_date = str_replace("Z","",$birth_date);

    // Zerlege das Datum in drei Teile
    // Beispiel: 1986-09-07 wird wie folgt zerlegt:
    // $birth_date_parts[0] = 1986
    // $birth_date_parts[1] = 09
    // $birth_date_parts[2] = 07
    $birth_date_parts = explode('-', $birth_date);

    // Setze das Datum neu zusammen
    // Beispiel: 1986-09-07 wird zu 07091986
    $new_password = $birth_date_parts[2].$birth_date_parts[1].$birth_date_parts[0];

    // Setze das Password für den Identity-Service
    $data['password'] = $new_password;
    // Setze das Password für die Pin-Nummer (OPAC)
    $data['pin_number'] = $new_password;

    // Konvertieren der Datenstruktur zurück in JSON
    $jsonData = json_encode($data);

    // DEBUG: Einkommentieren, um die zu sendenden Daten zu sehen
    // print_r($jsonData);

    // Optionen für den erneuten Request
    $url = "https://api-eu.hosted.exlibrisgroup.com/almaws/v1/users/$user_id?user_id_type=all_unique&apikey=$api_key&override=pin_number";

    $sendOptions = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'PUT',
            'ignore_errors' => true,
            'content' => $jsonData
        )
    );

    // Erzeugen des erneuten Request-Kontexts
    $sendContext = stream_context_create($sendOptions);

    // Senden der geänderten JSON-Daten
    $sendResponse = file_get_contents($url, false, $sendContext);
    
   // Auswerten ob die Anfrage erfolgreich war
    if($http_response_header[0] == "HTTP/1.1 200 OK"){
        return "$user_id : Setze Kennwort und Pin auf $new_password";
    }else{
        echo $sendResponse;
        return "$user_id : Es ist ein Fehler aufgetreten!";
    }
}
