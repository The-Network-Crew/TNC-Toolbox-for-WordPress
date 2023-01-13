#!/bin/bash

# Creates necessary folder/s and file/s to enable usage of the TNC WP Toolbox plugin.
# Will create an unrestricted cPanel API Token and store this for the plugin to auth.

for user in `\ls -A1 /var/cpanel/users/`
do
  mkdir -p /home/${user}/.tnc/
  echo ${user} > /home/${user}/.tnc/cp-username
  uapi --output=jsonpretty --user=${user} Tokens create_full_access name='TNC-WP-TOOLBOX' | jq -r '.result.data.token' > /home/${user}/.tnc/cp-api-key
  chown ${user}:${user} -R /home/${user}/.tnc/
  chmod 0400 /home/${user}/.tnc/cp-api-key
done
