#! /usr/bin/env php
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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config.defaults.php';
require_once __DIR__ . '/config.php';

foreach (glob(__DIR__ . '/data/verified/*') as $node_file) {
  $nodename = basename($node_file);

  if (false === $email_to = @file_get_contents(__DIR__ . '/data/mail/' . $nodename))
    continue;

  system('ping6 -c5 -W5 ' . $nodename . '.' . $config['domain_suffix'] . ' >/dev/null 2>&1', $return_val);

  if ($return_val == 0)
    file_put_contents($node_file, '1');
  else {
    if (trim(file_get_contents($node_file)) != 0) {
      $mail = new PHPMailer;
      $mail->isSendmail();
      $mail->CharSet = 'utf-8';
      $mail->setFrom($config['email_from']);
      $mail->addAddress($email_to);
      $mail->isHTML(false);
      $mail->Subject = "[Nodewatcher] {$nodename} ist offline";
      $mail->Body = str_replace(array('___NODENAME___', '___EMAIL___'), array($nodename, $email_to), $config['email_message_offline']);
      $mail->send();
    }

    file_put_contents($node_file, '0');
  }
}
