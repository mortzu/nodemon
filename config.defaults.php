<?php

$config['view_additional_css'] = array();
$config['view_footer'] = '';
$config['view_header'] = '';
$config['view_header_pre'] = '';
$config['view_title'] = 'Nodewatcher';
$config['view_text'] = <<<EOF
Hiermit kannst du dich per E-Mail benachrichtigen lassen,
falls dein Freifunk-Knoten ausfällt.<br><br>
Trage hierfür den Knotennamen in das Feld ein, danach bekommst du eine Bestätigungsmail,
um deine E-Mail-Adresse auf Gültigkeit zu prüfen. Danach bekommst du eine E-Mail, wenn dein
Knoten ausfällt.
EOF;
$config['view_text_confirmed'] = 'Die E-Mail-Adresse für deinen Knoten wurde bestätigt.';
$config['view_text_node_noconnect'] = 'Konnte keine Information vom Knoten holen';
$config['view_text_node_parse'] = 'Konnte JSON vom Knoten nicht parsen';
$config['view_text_node_nomail'] = 'Auf dem Knoten wurde keine E-Mail-Adresse eingegeben';
$config['view_text_node_novalidmail'] = 'Auf dem Knoten wurde keine gültige E-Mail-Adresse eingegeben';
$config['view_text_node_already'] = 'Das Monitoring für diesen Knoten wurde schon eingerichtet';
$config['view_text_confirmation'] = 'Du hast eine E-Mail mit einem Bestätigungslink bekommen. Bitte klicke auf den Link.';
$config['domain_suffix'] = 'nodes.example.com';
$config['email_from'] = 'freifunk@example.com';
$config['email_subject_confirmation'] = '[Nodewatcher] Mailbestätigung';
$config['email_message_confirmation'] = <<<EOF
Du hast dich beim Nodewatcher angemeldet.
Um diesen nutzen zu können, musst Du deine Mailadresse
bestätigen:

___LINK___

-- 
Du erhältst diese Mail, weil die E-Mail-Adresse ___EMAIL___ als Kontakt bei der
Einrichtung dieses Knotens angegeben wurde.

EOF;
$config['email_subject_offline'] = '[Nodewatcher] ___NODENAME___ ist offline';
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
