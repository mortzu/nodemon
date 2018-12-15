<?php

$config['domain_suffix'] = 'nodes.example.com';
$config['email_from'] = 'freifunk@example.com';
$config['email_message_confirmation'] = <<<EOF
Sie haben sich beim Nodewatcher angemeldet.
Um diesen nutzen zu können, müssen Sie Ihre Mailadresse
bestätigen:

___LINK___


EOF;
$config['email_message_offline'] = <<<EOF
Hallo,

dein Knoten ___NODENAME___ ist offline.

Vielleicht ist er nicht mehr in Reichweite eines benachbarten Knoten oder seine
VPN-Verbindung ist abgebrochen?

-- 
Du erhältst diese Mail, weil die E-Mail-Adresse ___EMAIL___ als Kontakt bei der
Einrichtung dieses Knotens angegeben wurde. Du erhältst diese Mail nur einmal
pro Ausfall des Knotens.

Solltest du für diesen oder alle deine Knoten keine solchen Mails mehr erhalten
wollen, teil uns das bitte als Antwort auf diese Mail mit.
EOF;
