<?php

/**
 * @file
 * admin_actions.features.inc
 */

/**
 * Implements hook_views_api().
 *
 * @inheritdoc
 */
function admin_actions_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
