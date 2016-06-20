'use strict';

module.exports = function(grunt) {
	var path = require('path');
	var banner = '/**\n * <%= pkg.homepage %>\n * Copyright (c) <%= grunt.template.today("yyyy") %>\n * This file is generated automatically. Do not edit.\n */\n';

	require('load-grunt-config')(grunt, {
		// path to task.js files
		configPath: path.join(process.cwd(), 'client/grunt'),
	});
};