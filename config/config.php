<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    /**
     *  The field on your user's table where we can determine 
     * if the person authenticating is an an admin.
     */
    'permission_field' => 'role',
    /**
     * The value we should compare against the premission_column to determine 
     * if the person authenticating is an an admin.
     */
    'permission_value' => 'admin',
];