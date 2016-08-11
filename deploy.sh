#!/bin/bash
set -o errexit
COMPRESSOR="gzip -9"
if [ "$(which zopfli)" ]; then 
	COMPRESSOR="zopfli"
fi
set -o xtrace

jekyll clean
gulp

find _site/ -name '*.html' -o -name '*.css' \
         -o -name '.js'    -o -name '.jpg' \
         -o -name '.png'   -o -name '.svg' \
     | xargs -I {} sh -c "$COMPRESSOR {} -c > {}.zgz"

rsync -rvz --exclude config.php --exclude vendor ./_site/ nejsconf.com:/home/public/
ssh nejsconf.com 'cd /home/public/register && /usr/local/bin/php composer.phar install'


if [ "$NEJS_DEPLOY_HOOK" != "" ]; then
	curl -X POST --data-urlencode 'payload={"username":"DeployBot","icon_emoji":":ghost:","text":"><https://nejsconf.com|nejsconf.com> deployed!"}' $NEJS_DEPLOY_HOOK
fi
