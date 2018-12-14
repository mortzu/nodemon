#! /usr/bin/env bash
# 2018, mortzu <mortzu@gmx.de>.
# All rights reserved.

# Redistribution and use in source and binary forms, with or without modification, are
# permitted provided that the following conditions are met:
#
# * Redistributions of source code must retain the above copyright notice, this list of
#   conditions and the following disclaimer.
#
# * Redistributions in binary form must reproduce the above copyright notice, this list
#   of conditions and the following disclaimer in the documentation and/or other materials
#   provided with the distribution.
#
# * The names of its contributors may not be used to endorse or promote products derived
#   from this software without specific prior written permission.
#
# * Feel free to send Club Mate to support the work.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
# OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
# AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
# AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
# SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
# OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

# Set path to defaults
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

# Directory where the node files are stored
DATA_DIR="$(dirname $0)/data"

# Get DNS domain suffix from config
DOMAIN_SUFFIX="$(php -r "require_once '${DATA_DIR}/../config.php'; echo \$config['domain_suffix'] . \"\\n\";")"

# Get mail from address from config
EMAIL_FROM="$(php -r "require_once '${DATA_DIR}/../config.php'; echo \$config['email_from'] . \"\\n\";")"

# 
EMAIL_BODY="Hallo,

dein Knoten ___NODENAME___ ist offline.

Vielleicht ist er nicht mehr in Reichweite eines benachbarten Knoten oder seine
VPN-Verbindung ist abgebrochen?

-- 
Du erhältst diese Mail, weil die E-Mail-Adresse ___EMAIL___ als Kontakt bei der
Einrichtung dieses Knotens angegeben wurde. Du erhältst diese Mail nur einmal
pro Ausfall des Knotens.

Solltest du für diesen oder alle deine Knoten keine solchen Mails mehr erhalten
wollen, teil uns das bitte als Antwort auf diese Mail mit."

for NODE in ${DATA_DIR}/verified/*; do
  NODE_NAME="$(basename ${NODE})"

  if [ ! -e "${DATA_DIR}/mail/${NODE_NAME}" ]; then
    continue
  fi

  EMAIL_TO="$(<${DATA_DIR}/mail/${NODE_NAME})"

  if ping6 -c5 -W5 ${NODE_NAME}.${DOMAIN_SUFFIX} >/dev/null 2>&1; then
    echo 1 > "$NODE"
  else
    if [ "$(<"$NODE")" != 0 ]; then
      echo "$EMAIL_BODY" | \
        sed -e "s/___NODENAME___/${NODE_NAME}/g" \
            -e "s/___EMAIL___/${EMAIL_TO}/g" | \
        mailx -r "$EMAIL_FROM" -s "[Nodewatcher] ${NODE_NAME} ist offline" "$EMAIL_TO"
    fi

    echo 0 > "$NODE"
  fi
done
