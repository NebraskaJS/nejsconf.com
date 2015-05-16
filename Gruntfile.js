/*global module:false,require:false,console:false */
module.exports = function(grunt) {

	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Project configuration.
	grunt.initConfig({
		// Metadata.
		pkg: grunt.file.readJSON('package.json'),
		banner: '/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
			'<%= grunt.template.today("yyyy-mm-dd") %>\n' +
			'<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
			'* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author %>;' +
			' <%= pkg.license %> License */\n',
		config: {
			root: '', // from domain root, do not include the first slash, do include a trailing slash
			// See also: yaml.vars.baseurl
			jsSrc: '<%= config.root %>assets/js/',
			cssSrc: '<%= config.root %>assets/css/',
			imgSrc: '<%= config.root %>assets/img/',
			iconsSrc: '<%= config.imgSrc %>icons/',
			distFolder: '<%= config.root %>dist/<%= pkg.version %>/',
			distFeed: '<%- config.root %>_site/feed/atom.xml'
		},
		yaml: {
			file: '<%= config.root %>_config.yml',
			vars: {
				name: '<%= pkg.name %>',
				description: '<%= pkg.description %>',
				safe: false,
				baseurl: '/',
				markdown: 'rdiscount',
				// https://github.com/mojombo/jekyll/wiki/Permalinks
				permalink: '/:year/:title/',
				highlighter: 'pygments',
				relative_permalinks: false,
				distFolder: '/<%= config.distFolder %>',
				exclude: ['node_modules', 'Gruntfile.js']
			}
		},
		// Task configuration.
		concat: {
			options: {
				banner: '<%= banner %>',
				stripBanners: true
			},
			js: {
				src: ['<%= config.jsSrc %>initial.js'],
				dest: '<%= config.distFolder %>initial.js'
			},
			jsDefer: {
				src: ['<%= config.jsSrc %>defer.js'],
				dest: '<%= config.distFolder %>defer.js'
			}
			// CSS concat handled by SASS
		},
		uglify: {
			options: {
				banner: '<%= banner %>'
			},
			js: {
				src: '<%= concat.js.dest %>',
				dest: '<%= config.distFolder %>initial.min.js'
			},
			jsDefer: {
				src: '<%= concat.jsDefer.dest %>',
				dest: '<%= config.distFolder %>defer.min.js'
			}
		},
		jshint: {
			options: {
				curly: true,
				eqeqeq: true,
				immed: true,
				latedef: true,
				newcap: true,
				noarg: true,
				sub: true,
				undef: true,
				unused: true,
				boss: true,
				eqnull: true,
				browser: true,
				loopfunc: true,
				globals: {}
			},
			gruntfile: {
				src: 'Gruntfile.js'
			},
			js: {
				src: ['js/**/*.js']
			}
		},
		sass: {
			dist: {
				options: {
					trace: true,
					style: 'expanded',
					sourcemap: 'file'
				},
				files: {
					'<%= config.distFolder %>initial.css': '<%= config.cssSrc %>initial.scss',
					'<%= config.distFolder %>ie8.css': '<%= config.cssSrc %>ie8.scss'
				}
			}
		},
		cssmin: {
			dist: {
				options: {
					banner: '<%= banner %>'
				},
				files: {
					'<%= config.distFolder %>initial.min.css': ['<%= config.distFolder %>initial.css'],
					'<%= config.distFolder %>ie8.min.css': ['<%= config.distFolder %>ie8.css'],
					'<%= config.distFolder %>icons.min.css': ['<%= config.distFolder %>icons.css']
				}
			}
		},
		copy: {
			// For CSS inlining
			includes: {
				files: {
					'<%= config.root %>_includes/initial.min.css': ['<%= config.distFolder %>initial.min.css'],
					'<%= config.root %>_includes/initial.min.js': ['<%= config.distFolder %>initial.min.js']
				}
			}
		},
		grunticon: {
			icons: {
				files: [{
					expand: true,
					cwd: '<%= config.iconsSrc %>',
					src: [ '*.svg', '*.png' ],
					dest: '<%= config.distFolder %>icons/',
				}],
				options: {
					customselectors: {}
				}
			}
		},
		zopfli: {
			main: {
				options: {
					iteration: 15
				},
				files: [
					{
						expand: true,
						cwd: '<%= config.root %>_site/',
						src: ['**/*.html'],
						dest: '<%= config.root %>_site/',
						extDot: 'last',
						ext: '.html.zgz'
					},
					{
						expand: true,
						cwd: '<%= config.root %>_site/',
						src: ['**/*.js'],
						dest: '<%= config.root %>_site/',
						extDot: 'last',
						ext: '.js.zgz'
					},
					{
						expand: true,
						cwd: '<%= config.root %>_site/',
						src: ['**/*.css'],
						dest: '<%= config.root %>_site/',
						extDot: 'last',
						ext: '.css.zgz'
					},
					{
						expand: true,
						cwd: '<%= config.root %>_site/',
						src: ['**/*.svg'],
						dest: '<%= config.root %>_site/',
						extDot: 'last',
						ext: '.svg.zgz'
					}
				]
			}
		},
		htmlmin: {
			main: {
				options: {
					removeComments: true,
					collapseWhitespace: true
				},
				files: [
					{
						expand: true,
						cwd: '<%= config.root %>_site/',
						src: '**/*.html',
						dest: '<%= config.root %>_site/'
					}
				]
			}
		},
		shell: {
			jekyll: {
				command: 'jekyll build --config _config.yml',
				options: {
					stdout: true,
					execOptions: {
						cwd: '<%= config.root %>'
					}
				}
			}
		},
		clean: {
			js: [ '<%= config.root %>/_site/**/*.zgz' ]
		},
		watch: {
			assets: {
				files: ['<%= config.cssSrc %>**/*', '<%= config.jsSrc %>**/*', '<%= config.imgSrc %>**/*'],
				tasks: ['assets', 'content']
			},
			grunticon: {
				files: ['<%= config.iconsSrc %>**/*'],
				tasks: ['grunticon', 'content']
			},
			content: {
				files: [
					'<%= config.root %>*.php',
					'<%= config.root %>_posts/**/*',
					'<%= config.root %>_layouts/**/*',
					'<%= config.root %>license/**/*',
					'<%= config.root %>team/**/*',
					'<%= config.root %>sponsor/**/*',
					'<%= config.root %>index.*',
					'<%= config.root %>_plugins/**/*',
					'<%= config.root %>_includes/**/*'
				],
				tasks: ['content']
			},
			config: {
				files: ['Gruntfile.js'],
				tasks: ['config']
			}
		}
	});

	grunt.registerTask( 'yaml', function() {
		var output = grunt.config( 'yaml.file' ),
			vars = grunt.config( 'yaml.vars' ),
			fs = require('fs'),
			str = [ '# Autogenerated by `grunt config`' ];

		for( var j in vars ) {
			if( Array.isArray( vars[ j ] ) ) {
				str.push( j + ':' );
				vars[ j ].forEach(function( val ) {
					str.push( ' - ' + val );
				});
			} else {
				str.push( j + ': ' + vars[ j ] );
			}
		}

		var err = fs.writeFileSync( output, str.join( '\n' ) );
		if(err) {
			console.log(err);
		} else {
			console.log( output + ' write successful.');
		}
	});

	// Default task.
	grunt.registerTask('assets', ['sass', 'jshint', 'concat', 'uglify', 'cssmin']);
	grunt.registerTask('images', ['grunticon']);
	grunt.registerTask('config', ['yaml']);
	grunt.registerTask('content', ['copy:includes', 'shell:jekyll']);
	grunt.registerTask('default', ['clean', 'config', 'assets', 'images', 'content']);

	grunt.registerTask('stage', ['default', 'htmlmin', 'zopfli']);
	// Upload to Production
	// grunt stage && ./deploy.sh
};
