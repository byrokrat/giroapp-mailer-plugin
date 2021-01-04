COMPOSER_CMD=composer
PHIVE_CMD=phive

BOX_CMD=tools/box
PHPSTAN_CMD=tools/phpstan
PHPCS_CMD=tools/phpcs

GIROAPP_INSTALLED:=$(shell command -v giroapp 2> /dev/null)

.DEFAULT_GOAL=all

TARGET=giroapp-mailer-plugin.phar
SRC_FILES:=$(shell find src/ -type f -name '*.php')

$(TARGET): stub.php $(SRC_FILES) box.json composer.lock $(BOX_CMD)
	$(COMPOSER_CMD) install --prefer-dist --no-dev
	$(BOX_CMD) compile
	$(COMPOSER_CMD) install

.PHONY: all build clean

all: phpstan phpcs build

build: $(TARGET)

clean:
	rm -f $(TARGET)
	rm -rf vendor

.PHONY: install uninstall

install: $(TARGET)
ifndef GIROAPP_INSTALLED
    $(error "giroapp is not available, please install to continue")
endif
	cp $< $(shell giroapp conf plugins_dir)

uninstall:
ifndef GIROAPP_INSTALLED
    $(error "giroapp is not available, please install to continue")
endif
	rm -f $(shell giroapp conf plugins_dir)/$(TARGET)

.PHONY: phpstan phpcs

phpstan: vendor/installed $(PHPSTAN_CMD)
	$(PHPSTAN_CMD) analyze -l 8 src stub.php

phpcs: vendor/installed $(PHPCS_CMD)
	$(PHPCS_CMD)

composer.lock: composer.json
	@echo composer.lock is not up to date

vendor/installed: composer.lock
	$(COMPOSER_CMD) install
	touch $@

tools/installed:
	$(PHIVE_CMD) install --force-accept-unsigned
	touch $@

$(BOX_CMD): tools/installed
$(PHPSTAN_CMD): tools/installed
$(PHPCS_CMD): tools/installed
