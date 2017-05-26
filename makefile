MAKEFLAGS+=--ignore-errors
MAKEFLAGS+=--no-print-directory
SHELL:=/bin/bash

.PHONY: build

build:
	cd .. \
	&& \
	/usr/bin/rm --force mlReferences/mlReferences.zip \
	&& \
	/usr/bin/zip \
		--recurse-paths \
		mlReferences.zip \
		mlReferences \
		-x '*.git*' \
		-x '*.gitignore*' \
		-x '*files*' \
		-x '*makefile*' \
		-x '*phpcs.xml*' \
	&& \
	/usr/bin/mv mlReferences.zip mlReferences \
	&& \
	cd mlReferences
