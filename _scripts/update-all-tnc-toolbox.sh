#!/bin/bash

# Only updates the TNC Toolbox installations found across the Server
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
    fixperms -a ${user}
    chmod 0700 ${homedir}/public_html/wp-content/tnc-toolbox-config/
    chmod 0600 ${homedir}/public_html/wp-content/tnc-toolbox-config/*
    chown ${user}:${user} -R ${homedir}/public_html/wp-content/tnc-toolbox-config/
  fi
  
done
