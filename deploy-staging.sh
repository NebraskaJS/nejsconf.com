echo "Note: Requires an \'nejsconfstaging\' host in .ssh/config";
rsync -avz ssh ./_site/ nejsconfstaging:/home/public/