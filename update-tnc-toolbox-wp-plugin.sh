#!/bin/bash

# Creates unrestricted cPanel API Tokens & updates the TNC Toolbox for WP.
# The Network Crew Pty Ltd :: https://github.com/LEOPARD-host/TNC-WP-Toolbox/

# NOTE: This script assumes a properly functional CageFS set-up on your server.
# CageFS: If you do not have this set-up, change/comment "wp" & install separately.
# fixperms: You need https://github.com/PeachFlame/cPanel-fixperms in /usr/local/sbin

# THIS SCRIPT IS TO UPDATE THE PLUGIN. For install, see the other bash script in repo.
# Between v1.0/v1.1 and v1.2, the config method changed. This script handles the update.

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Please run as root"
  exit
fi

# Check for jq, install if not present
rpm -qa | grep -qw jq || yum -y install jq

# DOCS: wp-cli syntax is here: https://developer.wordpress.org/cli/commands/plugin/install/
for user in `\ls -A1 /var/cpanel/users/`
do

  # Capture the home directory
  homedir=$(getent passwd ${user} | cut -d : -f 6)

  # Skip account if it isn't WordPress
  if [[ ! -f ${homedir}"/public_html/wp-includes/version.php" ]] ; then
    echo "SKIPPING USER: ${user} does not run WP."
    continue
  fi
  
  # Update plugin, if it exists; deploy config
  if [[ -d ${homedir}"/public_html/wp-content/plugins/tnc-toolbox/" ]] ; then
    echo "UPDATE: tnc-toolbox present in ${user}"
    su - ${user} -c "cd public_html && wp plugin update tnc-toolbox"
    mkdir -p ${homedir}/public_html/wp-content/tnc-toolbox-config/
    uapi --output=jsonpretty --user=${user} Tokens revoke name='TNC-TOOLBOX'
    uapi --output=jsonpretty --user=${user} Tokens revoke name='TNC-WP-TOOLBOX'
    uapi --output=jsonpretty --user=${user} Tokens create_full_access name='TNC-TOOLBOX' | jq -r '.result.data.token' > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key
    echo ${user} > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username
    hostname -f > ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname
    fixperms -a ${user}
    chmod 0600 ${homedir}/public_html/wp-content/tnc-toolbox-config/*
    echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username) > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username
    echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key) > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key
    echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname) > ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname
  fi
  
done
