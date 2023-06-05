Dieses Skript setzt bei Benutzern das Passwort für den Identity-Service und die Selbstverbucher-Pin auf TTMMJJJJ
Die Pin für den Selbstverbucher kann sowohl bei internen als auch externen Benutzern in Alma gesetzt werden.
Das Password für den Identity-Service kann nur bei internen Benutzern in Alma gesetzt werden.
Soll nur die Pin für die Selbstverbucher gesetzt werden, muss die Zeile:

Das Skript kann leicht angepasst werden, um auch andere Daten in einem Benutzersatz zu ändern. 

Für individuelle Anpassungen oder falls Sie das Skript von einem Dienstleister ausführen lassen wollen, 
wenden Sie sich bitte an die Harald Popke DV-Systeme GmbH:
harald.popke@hp-dv-systeme.de

Das Skript darf frei verwendet und geändert werden. Es unterliegt keinen Nutzungsbedingungen. 

Das Skript darf frei verwendet und geändert werden. Es unterliegt keinen Nutzungsbedingungen. 
    
!!Die Ausführung erfolgt auf eigene Gefahr!! 

Das Skript gibt pro bearbeiteten Benutzer eine Rückmeldung mit einer fortlaufenden Nummer:
    
Beispiel:
     1 - B41$0000009855 : Setze Kennwort und Pin auf 01121990";
     2 - B41$0000066894 : Setze Kennwort und Pin auf 12122000";
     3 - B41$0000015696 : hat kein Geburtsdatum und wird übersprungen!";

Sollte das Skript unterbrochen werden, kann so sehr einfach die user_ids.txt um die bereits bearbeiten Fälle gekürzt werden.

Aufruf des Skriptes: Einfach auf der Konsole "php alma-pin.php" eingeben.
