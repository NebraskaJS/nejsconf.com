## Local Development Setup

Prerequisites: Node.js and Ruby

1. Apache Configuration (Don’t forget to restart Apache)

		<VirtualHost *>
		ServerName nejsconf.com.local
		DocumentRoot "/PATH_TO_SITE/"
		Alias /web/ /PATH_TO_SITE/web/_site/
		</virtualHost>
		
1. Add to `/etc/hosts`

		127.0.0.1	nejsconf.com.local

1. [`gem install jekyll`](http://jekyllrb.com/docs/installation/) (requires 1.0+)
1. `gem install sass`
1. `gem install rdiscount`
1. `npm install`
1. `composer.phar install`
1. `grunt`

## Local Development Workflow

1. `grunt watch`
1. Open `http://nejsconf.com.local/`

To install new local npm packages, use `npm install PACKAGE_NAME --save-dev`

## Design Notes

### Colors

Background/Gold: #e9ca20
Dark Gold (Brown?): #c09801
White: #ffffff

### Fonts

Effra Heavy
Effra Heavy Italic
Effra Bold
Effra Medium

### Font Sizes

"NEJS CONF 2015": 72px (Heavy)
"JAVASCRIPT IN THE WILD": 48px (Heavy Italic)
Date: 32px (Heavy)

"GET NOTIFIED": 28px (Heavy)
"when tickets go on-sale": 16px (Medium)
"APPLY TO SPEAK" + "SPONSOR US": 18px (BOLD)

Footer text: 18px (Medium)
