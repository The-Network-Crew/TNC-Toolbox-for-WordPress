#!/bin/bash
# This script removes new lines from plugin config files, to avoid issues
# https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/
# (C) The Network Crew Pty Ltd - GPLv3 license enclosed

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Please run as root"
  exit
fi

# Remove blank lines from config files
for user in `\ls -A1 /var/cpanel/users/`
do

  # Capture the home directory
  homedir=$(getent passwd ${user} | cut -d : -f 6)

  # Skip account if it isn't WordPress
  if [[ ! -d ${homedir}"/public_html/wp-content/plugins/tnc-toolbox/" ]] ; then
    echo "SKIPPING USER: ${user} does not run TNC Toolbox."
    continue
  fi
  
  # Remove blank lines
  echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username) > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-username
  echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key) > ${homedir}/public_html/wp-content/tnc-toolbox-config/cpanel-api-key
  echo -n $(tr -d "\n" < ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname) > ${homedir}/public_html/wp-content/tnc-toolbox-config/server-hostname

  # Confirm completed for user
  echo "REMOVED BLANK LINES: From ${user}"

done
