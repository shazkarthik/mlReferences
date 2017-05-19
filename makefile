MAKEFLAGS+=--ignore-errors
MAKEFLAGS+=--no-print-directory
SHELL:=/bin/bash

.PHONY: build

build:
	cd .. \
	&& \
	/usr/bin/rm --force references_management.zip \
	&& \
	/usr/bin/zip --recurse-paths references_management.zip references_management -x '*.git*' -x '*.gitignore*' -x '*files*' -x '*makefile*' \
	&& \
	/usr/bin/mv references_management.zip references_management \
	&& \
	cd references_management
