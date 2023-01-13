#!/bin/bash

# Will create unrestricted cPanel API Tokens & store them for each install to use.
# The Network Crew Pty Ltd :: https://github.com/LEOPARD-host/TNC-WP-Toolbox/

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Please run as root"
  exit
fi

# Check for jq, install if not present
rpm -qa | grep -qw jq || yum -y install jq

# Loop through users to make dir/file, create API Token
for user in `\ls -A1 /var/cpanel/users/`
do
  mkdir -p /home/${user}/.tnc/
  uapi --output=jsonpretty --user=${user} Tokens create_full_access name='TNC-WP-TOOLBOX' | jq -r '.result.data.token' > /home/${user}/.tnc/cp-api-key
  chown ${user}:${user} -R /home/${user}/.tnc/
  chmod 0600 /home/${user}/.tnc/cp-api-key
done
