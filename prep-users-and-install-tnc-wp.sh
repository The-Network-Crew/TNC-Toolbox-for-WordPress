#!/bin/bash

# Creates unrestricted cPanel API Tokens & then installs the TNC WP plugin.
# The Network Crew Pty Ltd :: https://github.com/LEOPARD-host/TNC-WP-Toolbox/

# NOTE: This script assumes a properly functional CageFS set-up on your server.
# CageFS: If you do not have this set-up, change/comment "wp" & install separately.

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Please run as root"
  exit
fi

# Check for jq, install if not present
rpm -qa | grep -qw jq || yum -y install jq

# Loop through users to make dir/file, create API Token, and install the plugin
# NOTE: You need to update the ZIP URL to be the tnc-wp-toolbox/ folder (only), zipped
# DOCS: wp-cli syntax is here: https://developer.wordpress.org/cli/commands/plugin/install/
for user in `\ls -A1 /var/cpanel/users/`
do
  mkdir -p /home/${user}/.tnc/
  uapi --output=jsonpretty --user=${user} Tokens create_full_access name='TNC-WP-TOOLBOX' | jq -r '.result.data.token' > /home/${user}/.tnc/cp-api-key
  chown ${user}:${user} -R /home/${user}/.tnc/
  chmod 0600 /home/${user}/.tnc/cp-api-key
  su - ${user} -c "cd public_html && wp plugin install https://insert.your.domain.here/plugin-only-zip-file.zip --activate"
done
