### Zoninator Expansion Pack client-side source files

This is the root directory of client-side source file assets. Grunt tasks will do the necessary operations to get them ready to be publically available in `static`, (aka `build` or `dist`). Available tasks are:

* `grunt watch`: Watch for changes to any source files and on change perform the related grunt tasks.
* `grunt build` or `grunt [default]`: Run all grunt operations on your source files.
* `grunt styles`: [Internal] Perform operations on the sass and sprite files, once.
* `grunt scripts`: [Internal] Perform operations on the javascript source, once.
* `grunt imagemin`: [Internal] Optimize images in the `static/images` dir, once.
