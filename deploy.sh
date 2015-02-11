echo "Note: Requires an \'nejsconf\' host in .ssh/config";
rsync -avz ssh ./_site/ nejsconf:/home/public/