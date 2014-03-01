module.exports = function(grunt) {
  grunt.initConfig({
    bpkg: grunt.file.readJSON('build/bower_components/bootstrap/package.json'),
    less: {
      bootstrap: {
        options: {
          strictMath: true,
          cleancss: true
        },
        files: {
          "public/css/bootstrap.min.css":"build/bootstrap/less/bootstrap.less"
        }
      }
    },

    usebanner: {
      bootstrap: {
        options: {
          position: 'top',
          banner: '/*!\n' +
                  ' * Bootstrap v<%= bpkg.version %> (<%= bpkg.homepage %>)\n' +
                  ' * Copyright 2011-<%= grunt.template.today("yyyy") %> <%= bpkg.author %>\n' +
                  ' * Licensed under <%= bpkg.license.type %> (<%= bpkg.license.url %>)\n' +
                  ' */\n',
        },
        files: {
          src: [
            'public/css/bootstrap.min.css',
          ]
        }
      }
    },

    cssmin: {
      compress: {
        options: {
          keepSpecialComments: '*',
          noAdvanced: true,
          report: 'min',
          compatibility: 'ie8'
        },
        src: 'build/bower_components/highlightjs/styles/default.css',
        dest: 'public/css/highlight.min.css'
      }
    },

    copy: {
      bootstrap_fonts: {
        expand: true,
        cwd: 'build/bower_components/bootstrap/dist/fonts/',
        src: '*',
        dest: 'public/fonts/'
      },
      bootstrap_js: {
        src: 'build/bower_components/bootstrap/dist/js/bootstrap.min.js',
        dest: 'public/js/bootstrap.min.js'
      },
      highlightjs: {
        src: 'build/bower_components/highlightjs/highlight.pack.js',
        dest: 'public/js/highlight.min.js'
      },
      jquery: {
        src: 'build/bower_components/jquery/dist/jquery.min.js',
        dest: 'public/js/jquery.min.js'
      },
      html5shiv: {
        src: 'build/bower_components/html5shiv/dist/html5shiv.js',
        dest: 'public/js/html5shiv.js'
      }
    }
  });

  grunt.loadNpmTasks('grunt-banner');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-less');

  grunt.registerTask('default', ['less', 'usebanner', 'cssmin', 'copy']);
};