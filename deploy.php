<?php
// TODO: Why aren't releases working

namespace Deployer;

require 'recipe/craftcms.php';
// Project name
set('application', '[Site/ProjectName]');

// Project repository
set('repository', 'git@github.com:[REPOSITORY].git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Set the default deploy environment to production
set('default_stage', 'production');

// Shared files/dirs between deploys 
set('shared_files', [
    '.env'
]);
set('shared_dirs', [
    'storage',
    'web/assets',
]);

// Writable dirs by web server 
set('writable_dirs', [
    'config/project',
    'storage',
    'web/assets',
    'web/cpresources',
]);
set('allow_anonymous_stats', false);

// Hosts

host('[YOUR SERVER IP ADDRESS]')
    ->set('remote_user', 'root')
    //Apache deploy path
    ->set('deploy_path', '/var/www/html/{{application}}')
    ->set('branch', 'master')
    ->setLabels([
        'type' => 'web',
        'env' => 'prod',
    ]);

// Tasks

desc('Execute migrations');
task('craft:migrate', function () {
    // TODO: Steps from https://github.com/nystudio107/devmode/blob/develop/buddy.yml#L94
    run('{{release_path}}/craft off --retry=60');
    // - "# Backup the database just in case any migrations or Project Config changes have issues"
    // - "php craft backup/db" ?
    // - "# Run pending migrations, sync project config, and clear caches"
    run('{{release_path}}/craft clear-caches/all');
    run('{{release_path}}/craft migrate/all');
    // originally: run('{{release_path}}/craft up');
    // - "# Turn Craft on"
    run('{{release_path}}/craft on');
})->once();

desc('Deploy your project');
task('deploy-now', [
    'deploy:info',
    'deploy:prepare',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success'
]);

// [Optional] Run migrations
//after('deploy:vendors', 'craft:migrate');
// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');


