grunt stage;
echo "Note: Requires an \'nejsconf\' host in .ssh/config";
# Run this as ./deploy.sh clobber to overwrite remote config with local
# By default this script will sync the remote config to local before deploy
if [ "$1" != "clobber" ]; then
scp nejsconf:/home/public/config.php ./_site/config.php
fi
rsync -avz ssh ./_site/ nejsconf:/home/public/
