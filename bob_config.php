<?php

namespace Bob\BuildConfig;

task('default', ['phar']);

desc('Run unit and feature tests');
task('test', ['phpspec', 'behat']);

desc('Run phpspec unit tests');
task('phpspec', ['update_container'], function() {
    shell('phpspec run');
    println('Phpspec unit tests passed');
});

desc('Run behat feature tests');
task('behat', ['update_container'], function() {
    shell('behat --stop-on-failure --suite=default');
    println('Behat feature tests passed');
});

desc('Run statical analysis using phpstan feature tests');
task('phpstan', function() {
    shell('phpstan analyze -c phpstan.neon -l 7 src');
    println('Phpstan analysis passed');
});

desc('Run php code sniffer');
task('sniff', function() {
    shell('phpcs src --standard=PSR2');
    println('Syntax checker on src/ passed');
});

desc('Build phar');
task('phar', function() {
    shell('composer install --prefer-dist --no-dev');
    shell('box compile');
    shell('composer install');
    println('Phar generation done');
});

desc('Globally install development tools');
task('install_dev_tools', function() {
    shell('composer global require consolidation/cgr');
    shell('cgr phpspec/phpspec');
    shell('cgr behat/behat');
    shell('cgr phpstan/phpstan');
    shell('cgr squizlabs/php_codesniffer');
    shell('cgr humbug/box --stability dev');
});

function shell(string $command)
{
    return sh($command, null, ['failOnError' => true]);
}
