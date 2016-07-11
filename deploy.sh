#!/bin/bash
set -o errexit
set -o xtrace
# Run this as ./deploy.sh clobber to overwrite remote config with local
# By default this script will sync the remote config to local before deploy

jekyll clean
gulp

if [ "$1" != "clobber" ]; then
	rsync -avz --exclude config.php --exclude vendor ./_site/ nejsconf.com:/home/public/
else
	rsync -avz --exclude vendor ./_site/ nejsconf.com:/home/public/
fi
ssh nejsconf.com 'cd /home/public/register && /usr/local/bin/php composer.phar install'
