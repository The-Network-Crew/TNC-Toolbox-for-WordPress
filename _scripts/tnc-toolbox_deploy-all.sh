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

# PRE-REQS: Shell must be enabled for accounts so they can be entered.
# PRE-REQS: WP-CLI must be installed, properly setup (ie. via CageFS).

# Require root to run
if [ "$EUID" -ne 0 ]
  then echo "Exiting: Must be run as root, on a properly-configured server"
  exit
fi

# Warn before going ahead deploying server-wide
echo "This will deploy tnc-toolbox server-wide on all WP instances!"
echo "You have 5 seconds to Ctrl+C (abort)..."
sleep 5

# DOCS: wp-cli syntax is here: https://developer.wordpress.org/cli/commands/plugin/install/
for user in `\ls -A1 /var/cpanel/users/`
do
  # Capture the home directory
  homedir=$(getent passwd ${user} | cut -d : -f 6)

  # Skip account if it isn't WordPress
  if [[ ! -f ${homedir}"/public_html/wp-includes/version.php" ]] ; then
    echo "SKIPPING: ${user} does not run WP."
    continue
  fi

  # 1st: Update then re-activate & fix perms.
  if [[ -d ${homedir}"/public_html/wp-content/plugins/tnc-toolbox/" ]] ; then
    echo "UPDATING ${user}: tnc-toolbox found within WP"
    su - ${user} -c "cd public_html && wp plugin update tnc-toolbox && wp plugin activate tnc-toolbox"
  fi

  # 2nd: Delete any remaining artifact config directories
  if [[ -d ${homedir}"/public_html/wp-content/tnc-toolbox-config/" ]] ; then
    echo "ARTIFACT FOUND, DELETING FOR: ${user}"
    rm -rf ${homedir}"/public_html/wp-content/tnc-toolbox-config/"
  fi

  # 3rd: Install if WP=yes and TNC=no (ie. compatible but not yet present)
  if [[ ! -d ${homedir}"/public_html/wp-content/plugins/tnc-toolbox/" ]] ; then
    echo "DEPLOYING ${user}: tnc-toolbox being installed..."
    su - ${user} -c "cd public_html && wp plugin install tnc-toolbox && wp plugin activate tnc-toolbox"
  fi
done

echo ""
echo "Done! You will need to configure each TNC Toolbox installation to access the cPanel UAPI."
echo "Note: If you wish to use Selective Purging, additional work required - see README.md. Ta."
echo ""
