module.exports = function(grunt) {
	// Project configuration.
	grunt.initConfig({
		folder : {
			dist        : 'www/media',
			build       : 'build_tmp'
		},

		// Let's clean up the system
		clean: {
			assets: {
				src: [
					'www/media/css/*',
					'www/media/fonts/*',
					'www/media/js/*'
				],
				expand: true,
				options: {
					force: true
				}
			},
			temp: { src: [ 'build_tmp/*' ], expand: true, options: { force: true } }
		},

		// Compile Sass source files to CSS
		sass: {
			dist: {
				options: {
					precision: '5',
					sourceMap: false // SHOULD BE FALSE FOR DIST
				},
				files: {
					'media_src/css/template.css': 'media_src/scss/template.scss'
				}
			}
		},

		// Initiate task after CSS is generated
		postcss: {
			options: {
				map: false,
				processors: [
					require('autoprefixer')({ browsers: 'last 2 versions' })
				],
			},
			dist: {
				src: 'media_src/css/template.css'
			}
		},

		// Let's minify some css files
		cssmin: {
			allCss: {
				files: [{
					expand: true,
					matchBase: true,
					ext: '.min.css',
					cwd: 'media_src/css',
					src: ['*.css', '!*.min.css'],
					dest: 'www/media/css/',
				}]
			}
		},

		// Transfer all the assets to media/vendor
		copy: {
			fromSource: {
				files: [
					{ 
						expand: true,
						cwd: 'node_modules/font-awesome/fonts',
						src: ['*'],
						dest: 'www/media/fonts/',
						filter: 'isFile'
					},
				]
			}
		},

		concat: {
			options: {
				separator: ';',
			},
			dist: {
				src: ['node_modules/smooth-scroll/dist/js/smooth-scroll.js', 'media_src/js/template.js'],
				dest: 'www/media/js/template.js',
			},
		},

		// Minimize some javascript files
		uglify: {
			allJs: {
				files: [
					{
						src: [
							'www/media/js/*.js',
						],
						dest: '',
						expand: true,
						ext: '.min.js'
					}
				]
			}
		},


	});

	// Load required modules
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-postcss');

	grunt.registerTask('default',
		[
			'clean:assets',
			'sass:dist',
			'postcss',
			'cssmin:allCss',
			'copy:fromSource',
			'concat:dist',
			'uglify:allJs',
		]
	);
};
