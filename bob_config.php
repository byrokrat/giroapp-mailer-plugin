<?php

namespace Bob\BuildConfig;

task('default', ['phpstan', 'sniff', 'phar']);

desc('Run statical analysis using phpstan feature tests');
task('phpstan', function() {
    shell('phpstan analyze -l 7 src');
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
    shell('cgr phpstan/phpstan');
    shell('cgr squizlabs/php_codesniffer');
    shell('cgr humbug/box --stability dev');
});

function shell(string $command)
{
    return sh($command, null, ['failOnError' => true]);
}
