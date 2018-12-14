<?php

/*
2018, mortzu <mortzu@gmx.de>.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of
  conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list
  of conditions and the following disclaimer in the documentation and/or other materials
  provided with the distribution.

* The names of its contributors may not be used to endorse or promote products derived
  from this software without specific prior written permission.

* Feel free to send Club Mate to support the work.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>Nodewatcher - Freifunk Bremen</title>
  </head>

  <body>
    <div class="container">
      <div class="bg-white p-4 p-md-5 p-lg-6">
        <h1>Nodewatcher</h1>

        <p>Lorem Ipsum</p>

<?php

require_once __DIR__ . '/config.php';

$message = <<<EOF
Sie haben sich beim Nodewatcher angemeldet.
Um diesen nutzen zu koennen, muessen Sie Ihre Mailadresse
bestaetigen:

___LINK___


EOF;

if (isset($_GET['token']) && !empty($_GET['token'])) {
  if ($dh = opendir(__DIR__ . '/data/pending/')) {
    while (($file = readdir($dh)) !== false) {
      if (!is_file(__DIR__ . '/data/pending/' . $file))
        continue;

      if ($_GET['token'] == trim(file_get_contents(__DIR__ . '/data/pending/' . $file))) {
        echo '<div class="alert alert-danger" role="alert">Bestaetigt</div>';
        rename(__DIR__ . '/data/pending/' . $file, __DIR__ . '/data/verified/' . $file);
      }
    }

    closedir($dh);
  }
} elseif (isset($_POST['nodename']) && !empty($_POST['nodename'])) {
  $nodename = preg_replace('/[^A-Za-z0-9-]/', '', $_POST['nodename']);

  if (false === $nodeinfo = file_get_contents('http://' . $nodename . '.' . $config['domain_suffix'] . '/cgi-bin/status')) {
    if (false === $nodeinfo = file_get_contents('http://' . $nodename . '.' . $config['domain_suffix'] . '/cgi-bin/nodeinfo'))
      echo '<div class="alert alert-danger" role="alert">Konnte keine Information vom Knoten holen</div>';
    elseif (NULL === $nodeinfo_json = json_decode($nodeinfo, true))
      echo '<div class="alert alert-danger" role="alert">Konnte JSON vom Knoten nicht parsen</div>';
    elseif (!isset($nodeinfo_json['owner']['contact']) || empty($nodeinfo_json['owner']['contact']))
      echo '<div class="alert alert-danger" role="alert">Auf dem Knoten wurde keine E-Mail-Adresse eingegeben</div>';
    else
      $nodecontact = $nodeinfo_json['owner']['contact'];
  } elseif (false === preg_match('/<dt>Contact<\/dt><dd>(.*)<\/dd>/', $nodeinfo, $nodecontact_array))
      echo '<div class="alert alert-danger" role="alert">Auf dem Knoten wurde keine E-Mail-Adresse eingegeben</div>';
  else
    $nodecontact = $nodecontact_array[1];

  if (!empty($nodecontact)) {
    if (!filter_var($nodecontact, FILTER_VALIDATE_EMAIL))
      echo '<div class="alert alert-danger" role="alert">Auf dem Knoten wurde keine gueltige E-Mail-Adresse eingegeben</div>';
    elseif (file_exists(__DIR__ . '/data/pending/' . $nodename))
      echo '<div class="alert alert-danger" role="alert">Das Monitoring fuer diesen Knoten wurde schon eingerichtet</div>';
    else {
      echo '<div class="alert alert-warning" role="alert">Du hast eine E-Mail mit einem Bestaetigungslink bekommen. Bitte klicke auf den Link.</div>';

      $token = md5(time());

      file_put_contents(__DIR__ . '/data/pending/' . $nodename, $token);
      file_put_contents(__DIR__ . '/data/mail/' . $nodename, $nodecontact);

      $header = array('From' => $config['email_from']);
      mail($nodecontact, '[Nodewatcher] Mailbestaetigung', str_replace('___LINK___', $_SERVER['SCRIPT_URI'] . '?token=' . $token, $message, $header));
    }
  }
} else {

?>

        <form class="form-inline" method="post" action="<?php echo $_SERVER['SCRIPT_URI']; ?>">
          <div class="form-group mx-sm-3">
            <input type="text" class="form-control" id="nodename" placeholder="Nodename" name="nodename">
          </div>
          <button type="submit" class="btn btn-primary">Eintragen</button>
        </form>

<?php } ?>

      </div>
    </div>
  </body>
</html>
