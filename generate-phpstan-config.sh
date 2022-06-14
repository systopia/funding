#!/bin/sh

ext_dir=$(dirname $PWD)
if [ $(basename $ext_dir) != "ext" ]; then
    echo "Parent directory is not an CiviCRM extension directory" >&2
    exit 1
fi

cat <<EOF
includes:
	- phpstan.neon.dist

parameters:
	scanDirectories:
		- $ext_dir/de.systopia.identitytracker/CRM/
EOF

