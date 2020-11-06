BOX:=$(shell command -v box 2> /dev/null)
GIROAPP:=$(shell command -v giroapp 2> /dev/null)
PHPSTAN:=$(shell command -v phpstan 2> /dev/null)
PHPCS:=$(shell command -v phpcs 2> /dev/null)
COMPOSER_CMD:=$(shell command -v composer 2> /dev/null)

.DEFAULT_GOAL=all

TARGET=giroapp-mailer-plugin.phar
SRC_FILES:=$(shell find src/ -type f -name '*.php')

$(TARGET): stub.php $(SRC_FILES) box.json composer.lock
ifndef BOX
    $(error "box is not available, please install to continue")
endif
ifndef COMPOSER_CMD
    $(error "composer is not available, please install to continue")
endif
	composer install --prefer-dist --no-dev
	box compile
	composer install

.PHONY: all build clean

all: phpstan phpcs build

build: $(TARGET)

clean:
	rm -f $(TARGET)
	rm -rf vendor

.PHONY: install uninstall

install: $(TARGET)
ifndef GIROAPP
    $(error "giroapp is not available, please install to continue")
endif
	cp $< $(shell giroapp conf plugins_dir)

uninstall:
ifndef GIROAPP
    $(error "giroapp is not available, please install to continue")
endif
	rm -f $(shell giroapp conf plugins_dir)/$(TARGET)

.PHONY: phpstan phpcs

phpstan: vendor/installed
ifndef PHPSTAN
    $(error "phpstan is not available, please install to continue")
endif
	phpstan analyze -l 8 src stub.php

phpcs: vendor/installed
ifndef PHPCS
    $(error "phpcs is not available, please install to continue")
endif
	phpcs src --standard=PSR2

composer.lock: composer.json
	@echo composer.lock is not up to date

vendor/installed: composer.lock
ifndef COMPOSER_CMD
    $(error "composer is not available, please install to continue")
endif
	composer install
	touch $@
