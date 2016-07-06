# Run this as ./deploy.sh clobber to overwrite remote config with local
# By default this script will sync the remote config to local before deploy
if [ "$1" != "clobber" ]; then
	echo "# Saving config"
	scp nejsconf.com:/home/public/register/config.php ./_site/register/config.php
fi
echo "# Syncing Files"
rsync -avz ssh ./_site/ nejsconf.com:/home/public/
echo "# Updating Composer"
ssh nejsconf.com 'cd /home/public/register && /usr/local/bin/php composer.phar install'
