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
DOMAIN_SUFFIX="$(php -r "require_once '${DATA_DIR}/../config.defaults.php'; require_once '${DATA_DIR}/../config.php'; echo \$config['domain_suffix'] . \"\\n\";")"

# Get from address for mail from config
EMAIL_FROM="$(php -r "require_once '${DATA_DIR}/../config.defaults.php'; require_once '${DATA_DIR}/../config.php'; echo \$config['email_from'] . \"\\n\";")"

# Get content for node-offline-email
EMAIL_MESSAGE_OFFLINE="$(php -r "require_once '${DATA_DIR}/../config.defaults.php'; require_once '${DATA_DIR}/../config.php'; echo \$config['email_message_offline'] . \"\\n\";")"

# Iterate over verified nodes directory
for NODE_FILE in ${DATA_DIR}/verified/*; do
  # Get node name from file name
  NODE_NAME="$(basename ${NODE_FILE})"

  # If no mail address set skip
  if [ ! -e "${DATA_DIR}/mail/${NODE_NAME}" ]; then
    continue
  fi

  # Get mail address
  EMAIL_TO="$(<${DATA_DIR}/mail/${NODE_NAME})"

  # Check if node is offline
  if ping6 -c5 -W5 ${NODE_NAME}.${DOMAIN_SUFFIX} >/dev/null 2>&1; then
    # Write status to node file
    echo 1 > "$NODE_FILE"
  else
    # If message was not already sent...
    if [ "$(<"$NODE_FILE")" != 0 ]; then
      # ... send mail
      echo "$EMAIL_MESSAGE_OFFLINE" | \
        sed -e "s/___NODENAME___/${NODE_NAME}/g" \
            -e "s/___EMAIL___/${EMAIL_TO}/g" | \
        mailx -S sendcharsets=utf-8 -r "$EMAIL_FROM" -s "[Nodewatcher] ${NODE_NAME} ist offline" "$EMAIL_TO"
    fi

    # Write status to node file
    echo 0 > "$NODE_FILE"
  fi
done
