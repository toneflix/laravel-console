<?php

/*
 * You can place your custom package configuration in here.
 */

use App\Models\v1\User;

return [
    'user_model' => User::class,

    /**
     *  The field on your user's table where we can determine
     * if the person authenticating is an an admin.
     */
    'permission_field' => 'privileges',

    /**
     * The value we should compare against the premission_column to determine
     * if the person authenticating is an an admin.
     */
    'permission_value' => 'admin',

    /**
     * The disk where your backups will be stored.
     */
    'backup_disk' => 'google',

    /**
     * Absolute paths to all directories that should be backed up.
     * These paths will also be backed up along with all files in you
     * filesystem.links config.
     */
    'paths' => [
    ],

    /**
     * The basee seeder that calls all your other seeders
     */
    'base_seeder' => 'DatabaseSeeder',

    /**
     * To receive notifications when the system is modified or backed up, you must
     * provide a slack webhook url.
     */
    'slack_webhook_urls' => [
        'default' => env('SLACK_ALERT_WEBHOOK'),
    ],
];