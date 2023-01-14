<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/jiannei/jarvis.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
host('jarvis.coderplanets.cn')
    ->setRemoteUser('deployer')
    ->setPort(22)
    ->setidentityFile('~/.ssh/deploykey')
    ->setForwardAgent(true)
    ->setSshMultiplexing(true)
//    ->setSshArguments(['-o UserKnownHostsFile=/dev/null'])
    ->set('http_user', 'www')
    ->set('branch', 'main')
    ->set('deploy_path', '/www/wwwroot/jarvis');

// Tasks
task('opcache:reset', function () {
    run('{{bin/php}} -r \'opcache_reset();\'');
});

// Hooks

after('deploy', 'opcache:reset');
after('deploy', 'artisan:queue:restart');
after('deploy:failed', 'deploy:unlock');
