#!/bin/bash

#    TNC Toolbox: Web Performance (for WordPress)
#    
#    Copyright (C) The Network Crew Pty Ltd (TNC)
#    PO Box 3113 Uki 2484 NSW Australia https://tnc.works
#
#    https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <https://www.gnu.org/licenses/>.

# PRE-REQS: Shell must be enabled for accounts. WP-CLI must be installed.
# PRE-REQS: PeachFlame's fixperms must be installed in /usr/local/sbin etc.
# DELETION: This script will RETAIN configs it can, then DELETE any artifacts.

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Exiting: Must be run as root, on a properly-configured server"
  exit
fi

# DOCS: wp-cli syntax is here: https://developer.wordpress.org/cli/commands/plugin/install/
for user in `\ls -A1 /var/cpanel/users/`
do
  # Capture the home directory
  homedir=$(getent passwd ${user} | cut -d : -f 6)

  # Update then re-activate & fix perms.
  if [[ -d ${homedir}"/public_html/wp-content/plugins/tnc-toolbox/" ]] ; then
    echo "UPDATING ${user}: tnc-toolbox found within WP"
    su - ${user} -c "cd public_html && wp plugin update tnc-toolbox && wp plugin activate tnc-toolbox"
    fixperms -a ${user}
  fi

  # Delete any remaining artifact config directories
  if [[ -d ${homedir}"/public_html/wp-content/tnc-toolbox-config/" ]] ; then
    echo "ARTIFACT FOUND, DELETING FOR: ${user}"
    echo "You have 3 seconds to Ctrl+C (abort)..."
    sleep 3
    rm -rf ${homedir}"/public_html/wp-content/tnc-toolbox-config/"
  fi
done
