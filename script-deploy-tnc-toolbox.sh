#!/bin/bash

# Creates unrestricted cPanel API Tokens & then installs the TNC WP Toolbox
# https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/
# (C) The Network Crew Pty Ltd - GPLv3 license enclosed

# NOTE: This script assumes a properly functional CageFS set-up on your server.
# CageFS: If you do not have this set-up, change/comment "wp" & install separately.
# fixperms: You need https://github.com/PeachFlame/cPanel-fixperms in /usr/local/sbin

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Please run as root"
  exit
fi

# Check for jq, install if not present
rpm -qa | grep -qw jq || yum -y install jq

# Loop through users to make dir/file, create API Token, and install the plugin
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
  
  # Remove plugin, if it exists
  if [[ -d ${homedir}"/public_html/wp-content/plugins/tnc-toolbox/" ]] ; then
    echo "UNINSTALL: tnc-toolbox present in ${user}"
    su - ${user} -c "cd public_html && wp plugin deactivate tnc-toolbox"
    su - ${user} -c "cd public_html && wp plugin delete tnc-toolbox"
    uapi --output=jsonpretty --user=${user} Tokens revoke name='TNC-TOOLBOX'
  fi

  # Install the plugin
  su - ${user} -c "cd public_html && wp plugin install tnc-toolbox --activate"

  # Create the config dir
  mkdir -p ${homedir}/public_html/wp-content/tnc-toolbox-config/

  # Generate the API Key
  uapi --output=jsonpretty --user=${user} Tokens create_full_access name='TNC-TOOLBOX' | jq -r '.result.data.token' > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key

  # Echo out the username
  echo ${user} > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username

  # Save the server hostname
  hostname -f > ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname

  # fixperms the account (ownership)
  fixperms -a ${user}

  # Internet-protect the config
  chmod 0600 ${homedir}/public_html/wp-content/tnc-toolbox-config/*
  chown ${user}:${user} -R ${homedir}/public_html/wp-content/tnc-toolbox-config/
  
  # Remove blank lines
  echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username) > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username
  echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key) > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key
  echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname) > ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname

done
