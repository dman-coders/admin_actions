<?php
/**
 * @file
 * admin_actions.features.inc
 */

/**
 * Implements hook_views_api().
 *
 * Lets Views know we supply a plugin.
 */
function admin_actions_views_api($module = NULL, $api = NULL) {
  return array(
    "api" => "3.0",
  );
}
